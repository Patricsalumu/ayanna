<?php

namespace App\Services;

use App\Models\Compte;
use App\Models\Commande;
use App\Models\EcritureComptable;
use App\Models\JournalComptable;
use App\Models\Paiement;
use App\Models\EntreeSortie;
use App\Models\PointDeVente;
use App\Models\StockJournalier;
use App\Models\ModePaiement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComptabiliteService
{
    public function genererBrouillonsFermetureSession(
        PointDeVente $pointDeVente,
        string $date,
        string $session,
        Carbon $debut,
        Carbon $fin,
        int $userId
    ): array {
        Log::info('[Comptabilité Session] Génération demandée', [
            'point_de_vente_id' => $pointDeVente->id,
            'entreprise_id' => $pointDeVente->entreprise_id,
            'date' => $date,
            'session' => $session,
            'debut' => $debut->toDateTimeString(),
            'fin' => $fin->toDateTimeString(),
        ]);

        return DB::transaction(function () use ($pointDeVente, $date, $session, $debut, $fin, $userId) {
            $journaux = array_values(array_filter([
                $this->genererBrouillonVentesSession($pointDeVente, $date, $session, $debut, $fin, $userId),
                $this->genererBrouillonPaiementsSession($pointDeVente, $date, $session, $debut, $fin, $userId),
                $this->genererBrouillonStockSession($pointDeVente, $date, $session, $userId),
            ]));

            Log::info('[Comptabilité Session] Génération terminée', [
                'point_de_vente_id' => $pointDeVente->id,
                'session' => $session,
                'nombre_journaux' => count($journaux),
                'types' => array_map(fn ($journal) => $journal->type_operation, $journaux),
            ]);

            return $journaux;
        });
    }

    private function genererBrouillonVentesSession(PointDeVente $pointDeVente, string $date, string $session, Carbon $debut, Carbon $fin, int $userId): ?JournalComptable
    {
        if ($journal = $this->journalSessionExistant($pointDeVente, $date, $session, 'vente')) {
            Log::info('[Comptabilité Session] Brouillon vente déjà existant', ['journal_id' => $journal->id, 'session' => $session]);
            return $journal;
        }

        $commandes = Commande::with(['panier.client', 'panier.produits.categorie'])
            ->whereBetween('created_at', [$debut, $fin])
            ->whereHas('panier', fn ($query) => $query->where('point_de_vente_id', $pointDeVente->id))
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('journal_comptable as journal_existant')
                    ->whereColumn('journal_existant.commande_id', 'commandes.id')
                    ->where('journal_existant.type_operation', 'vente');
            })
            ->get();

        if ($commandes->isEmpty()) {
            Log::info('[Comptabilité Session] Aucune commande à comptabiliser', [
                'point_de_vente_id' => $pointDeVente->id,
                'date' => $date,
                'session' => $session,
                'debut' => $debut->toDateTimeString(),
                'fin' => $fin->toDateTimeString(),
            ]);
            return null;
        }

        Log::info('[Comptabilité Session] Commandes trouvées', [
            'session' => $session,
            'nombre' => $commandes->count(),
            'commande_ids' => $commandes->pluck('id')->all(),
        ]);

        $compteClient = $this->compteClientSession($pointDeVente);
        $dateLibelle = $this->dateLibelleSession($date);
        $debitsClients = [];
        $creditsVentes = [];

        foreach ($commandes as $commande) {
            $panier = $commande->panier;
            if (!$panier || $panier->produits->isEmpty()) {
                continue;
            }

            $montantBrut = 0.0;
            foreach ($panier->produits as $produit) {
                $quantite = (float) ($produit->pivot->quantite ?? 0);
                $prix = (float) ($produit->pivot->prix ?? $produit->prix_vente ?? 0);
                $montantLigne = round(max(0, $quantite) * max(0, $prix), 2);
                if ($montantLigne <= 0) {
                    continue;
                }

                $categorie = $produit->categorie;
                $compteVente = $this->comptePourClasse(
                    $categorie?->compte_vente_id,
                    $pointDeVente->entreprise_id,
                    '7',
                    "compte de vente de la catégorie du produit {$produit->nom}"
                );
                $creditsVentes[$categorie->id] = [
                    'compte_id' => $compteVente->id,
                    'categorie' => $categorie->nom,
                    'montant' => ($creditsVentes[$categorie->id]['montant'] ?? 0) + $montantLigne,
                ];
                $montantBrut += $montantLigne;
            }

            if ($montantBrut <= 0) {
                continue;
            }

            $clientId = $panier->client_id;
            $cleClient = $clientId ?? 'sans_client';
            $debitsClients[$cleClient] = [
                'client_id' => $clientId,
                'montant' => ($debitsClients[$cleClient]['montant'] ?? 0) + $montantBrut,
            ];
        }

        $lignes = [];
        foreach ($debitsClients as $client) {
            $lignes[] = [
                'compte_id' => $compteClient->id,
                'libelle' => 'Ventes du ' . $dateLibelle,
                'debit' => round($client['montant'], 2),
                'credit' => 0,
                'client_id' => $client['client_id'],
            ];
        }
        foreach ($creditsVentes as $vente) {
            $lignes[] = [
                'compte_id' => $vente['compte_id'],
                'libelle' => 'Vente ' . $vente['categorie'] . ' du ' . $dateLibelle,
                'debit' => 0,
                'credit' => round($vente['montant'], 2),
                'client_id' => null,
            ];
        }
        $total = array_sum(array_column($lignes, 'debit'));
        return $this->creerJournalBrouillonSession(
            $pointDeVente, $date, $session, $userId, 'vente',
            'Vente du ' . Carbon::parse($date)->format('d-m-Y') . ' - ' . $pointDeVente->nom, $total, $lignes
        );
    }

    private function genererBrouillonPaiementsSession(PointDeVente $pointDeVente, string $date, string $session, Carbon $debut, Carbon $fin, int $userId): ?JournalComptable
    {
        if ($journal = $this->journalSessionExistant($pointDeVente, $date, $session, 'paiement')) {
            Log::info('[Comptabilité Session] Brouillon paiement déjà existant', ['journal_id' => $journal->id, 'session' => $session]);
            return $journal;
        }

        $modes = ModePaiement::where('entreprise_id', $pointDeVente->entreprise_id)->get();
        $paiements = Paiement::with('commande.panier')
            ->where('statut', 'validé')
            ->whereBetween('created_at', [$debut, $fin])
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('journal_comptable as journal_existant')
                    ->whereColumn('journal_existant.paiement_id', 'paiements.id')
                    ->where('journal_existant.type_operation', 'paiement');
            })
            ->whereHas('commande.panier', fn ($query) => $query->where('point_de_vente_id', $pointDeVente->id))
            ->get();

        $commandesSession = Commande::with('panier')
            ->whereBetween('created_at', [$debut, $fin])
            ->whereHas('panier', fn ($query) => $query->where('point_de_vente_id', $pointDeVente->id))
            ->get();
        $remisesParClient = [];
        foreach ($commandesSession as $commande) {
            $panier = $commande->panier;
            $montantRemise = round(max(0, (float) ($panier?->total_remise ?? $panier?->remise ?? 0)), 2);
            if ($montantRemise <= 0) {
                continue;
            }

            $clientId = $panier->client_id;
            $cleClient = $clientId ?? 'sans_client';
            $remisesParClient[$cleClient] = [
                'client_id' => $clientId,
                'montant' => ($remisesParClient[$cleClient]['montant'] ?? 0) + $montantRemise,
            ];
        }

        if ($paiements->isEmpty() && empty($remisesParClient)) {
            Log::info('[Comptabilité Session] Aucun paiement à comptabiliser', [
                'point_de_vente_id' => $pointDeVente->id,
                'date' => $date,
                'session' => $session,
                'debut' => $debut->toDateTimeString(),
                'fin' => $fin->toDateTimeString(),
            ]);
            return null;
        }

        Log::info('[Comptabilité Session] Paiements trouvés', [
            'session' => $session,
            'nombre' => $paiements->count(),
            'paiement_ids' => $paiements->pluck('id')->all(),
        ]);

        $compteClient = $this->compteClientSession($pointDeVente);
        $dateLibelle = $this->dateLibelleSession($date);
        $paiementsParMode = [];
        $creditsClientsParMode = [];

        foreach ($paiements as $paiement) {
            $codeMode = $this->normaliserMode($paiement->mode);
            if ($codeMode === 'compte_client') {
                continue;
            }

            $mode = $modes->first(fn ($item) => $this->normaliserMode($item->code) === $codeMode)
                ?? $modes->first(fn ($item) => $this->normaliserMode($item->nom) === $codeMode);
            if (!$mode) {
                throw new \RuntimeException("Le mode de paiement « {$paiement->mode} » n'est pas configuré pour la comptabilité.");
            }

            $modeCode = $this->normaliserMode($mode->code);
            $modeNom = $this->normaliserMode($mode->nom);
            $estOffre = str_starts_with($modeCode, 'offre') || str_starts_with($modeNom, 'offre');
            $prefixClasse = $estOffre ? '6' : '5';
            $comptePaiement = $this->comptePourClasse(
                $mode->compte_id,
                $pointDeVente->entreprise_id,
                $prefixClasse,
                "compte du mode de paiement {$mode->nom}"
            );
            $montant = round((float) $paiement->montant, 2);
            if ($montant <= 0) {
                continue;
            }

            $cleMode = $mode->id;
            $paiementsParMode[$cleMode] = [
                'mode' => $mode,
                'compte_id' => $comptePaiement->id,
                'montant' => ($paiementsParMode[$cleMode]['montant'] ?? 0) + $montant,
            ];
            $clientId = $paiement->commande?->panier?->client_id;
            $cleClient = $cleMode . '|' . ($clientId ?? 'sans_client');
            $creditsClientsParMode[$cleClient] = [
                'mode' => $mode,
                'client_id' => $clientId,
                'montant' => ($creditsClientsParMode[$cleClient]['montant'] ?? 0) + $montant,
            ];
        }

        $lignes = [];
        foreach ($paiementsParMode as $paiementMode) {
            $libelle = $this->libellePaiementSession($paiementMode['mode'], $dateLibelle);
            $lignes[] = [
                'compte_id' => $paiementMode['compte_id'],
                'libelle' => $libelle,
                'debit' => round($paiementMode['montant'], 2),
                'credit' => 0,
                'client_id' => null,
            ];
        }
        foreach ($creditsClientsParMode as $client) {
            $lignes[] = [
                'compte_id' => $compteClient->id,
                'libelle' => $this->libellePaiementSession($client['mode'], $dateLibelle),
                'debit' => 0,
                'credit' => round($client['montant'], 2),
                'client_id' => $client['client_id'],
            ];
        }

        if (!empty($remisesParClient)) {
            $compteRemise = $this->comptePourClasse(
                $pointDeVente->compte_remise_id,
                $pointDeVente->entreprise_id,
                '6',
                'compte de remise sur ventes du point de vente'
            );
            $montantTotalRemises = array_sum(array_column($remisesParClient, 'montant'));
            $lignes[] = [
                'compte_id' => $compteRemise->id,
                'libelle' => 'Remise du ' . $dateLibelle,
                'debit' => round($montantTotalRemises, 2),
                'credit' => 0,
                'client_id' => null,
            ];

            foreach ($remisesParClient as $remise) {
                $lignes[] = [
                    'compte_id' => $compteClient->id,
                    'libelle' => 'Remise du ' . $dateLibelle,
                    'debit' => 0,
                    'credit' => round($remise['montant'], 2),
                    'client_id' => $remise['client_id'],
                ];
            }
        }

        $total = array_sum(array_column($lignes, 'debit'));
        return $this->creerJournalBrouillonSession(
            $pointDeVente, $date, $session, $userId, 'paiement',
            'Règlement de la session du ' . Carbon::parse($date)->format('d-m-Y'), $total, $lignes
        );
    }

    private function genererBrouillonStockSession(PointDeVente $pointDeVente, string $date, string $session, int $userId): ?JournalComptable
    {
        if ($journal = $this->journalSessionExistant($pointDeVente, $date, $session, 'ajustement')) {
            Log::info('[Comptabilité Session] Brouillon stock déjà existant', ['journal_id' => $journal->id, 'session' => $session]);
            return $journal;
        }

        $stocks = StockJournalier::with('produit.categorie')
            ->where('point_de_vente_id', $pointDeVente->id)
            ->where('date', $date)
            ->where('session', $session)
            ->get();
        Log::info('[Comptabilité Session] Lignes de stock trouvées', [
            'point_de_vente_id' => $pointDeVente->id,
            'date' => $date,
            'session' => $session,
            'nombre' => $stocks->count(),
            'quantite_vendue' => $stocks->sum('quantite_vendue'),
        ]);
        $dateLibelle = $this->dateLibelleSession($date);
        $debitsVariation = [];
        $debitsPertesAbimees = [];
        $creditsStockVendu = [];
        $creditsStockAbime = [];

        foreach ($stocks as $stock) {
            $quantiteVendue = (float) ($stock->quantite_vendue ?? 0);
            $quantiteAbimee = (float) ($stock->quantite_abimee ?? 0);
            if ($quantiteVendue <= 0 && $quantiteAbimee <= 0) {
                continue;
            }

            $produit = $stock->produit;
            if (!$produit) {
                throw new \RuntimeException('Le produit vendu ou abîmé est introuvable lors de la clôture comptable.');
            }

            $prixAchat = (float) $produit->prix_achat;
            if ($prixAchat < 0) {
                throw new \RuntimeException('Le prix d’achat ne peut pas être négatif.');
            }
            if ($prixAchat === 0.0) {
                Log::info('[Comptabilité Session] Produit vendu considéré gratuit', [
                    'produit_id' => $produit->id,
                    'produit' => $produit->nom,
                    'quantite_vendue' => $quantiteVendue,
                    'quantite_abimee' => $quantiteAbimee,
                ]);
                continue;
            }

            $categorie = $produit->categorie;
            $compteStock = $this->comptePourClasse(
                $categorie?->compte_stock_id,
                $pointDeVente->entreprise_id,
                '3',
                "compte de stock de la catégorie du produit {$produit->nom}"
            );
            $compteVariation = $this->comptePourClasse(
                $categorie?->compte_variation_stock_id,
                $pointDeVente->entreprise_id,
                '6',
                "compte de variation de stock de la catégorie du produit {$produit->nom}"
            );
            $montantVendu = round($quantiteVendue * $prixAchat, 2);
            $montantAbime = round($quantiteAbimee * $prixAchat, 2);

            if ($montantVendu > 0) {
                $debitsVariation[$categorie->id] = [
                    'compte_id' => $compteVariation->id,
                    'categorie' => $categorie->nom,
                    'montant' => ($debitsVariation[$categorie->id]['montant'] ?? 0) + $montantVendu,
                ];
                $creditsStockVendu[$categorie->id] = [
                    'compte_id' => $compteStock->id,
                    'categorie' => $categorie->nom,
                    'montant' => ($creditsStockVendu[$categorie->id]['montant'] ?? 0) + $montantVendu,
                ];
            }

            if ($montantAbime > 0) {
                $debitsPertesAbimees[$categorie->id] = [
                    'compte_id' => $compteVariation->id,
                    'categorie' => $categorie->nom,
                    'montant' => ($debitsPertesAbimees[$categorie->id]['montant'] ?? 0) + $montantAbime,
                ];
                $creditsStockAbime[$categorie->id] = [
                    'compte_id' => $compteStock->id,
                    'categorie' => $categorie->nom,
                    'montant' => ($creditsStockAbime[$categorie->id]['montant'] ?? 0) + $montantAbime,
                ];
            }
        }

        $lignes = [];
        foreach ($debitsVariation as $variation) {
            $lignes[] = [
                'compte_id' => $variation['compte_id'],
                'libelle' => 'Variation stock ' . $variation['categorie'] . ' du ' . $dateLibelle,
                'debit' => round($variation['montant'], 2),
                'credit' => 0,
                'client_id' => null,
            ];
        }
        foreach ($debitsPertesAbimees as $perte) {
            $lignes[] = [
                'compte_id' => $perte['compte_id'],
                'libelle' => 'Perte stock abîmé ' . $perte['categorie'] . ' du ' . $dateLibelle,
                'debit' => round($perte['montant'], 2),
                'credit' => 0,
                'client_id' => null,
            ];
        }
        foreach ($creditsStockVendu as $sortie) {
            $lignes[] = [
                'compte_id' => $sortie['compte_id'],
                'libelle' => 'Sortie stock vendu ' . $sortie['categorie'] . ' du ' . $dateLibelle,
                'debit' => 0,
                'credit' => round($sortie['montant'], 2),
                'client_id' => null,
            ];
        }
        foreach ($creditsStockAbime as $sortie) {
            $lignes[] = [
                'compte_id' => $sortie['compte_id'],
                'libelle' => 'Sortie stock abîmé ' . $sortie['categorie'] . ' du ' . $dateLibelle,
                'debit' => 0,
                'credit' => round($sortie['montant'], 2),
                'client_id' => null,
            ];
        }

        $total = array_sum(array_column($debitsVariation, 'montant'))
            + array_sum(array_column($debitsPertesAbimees, 'montant'));
        return $this->creerJournalBrouillonSession(
            $pointDeVente, $date, $session, $userId, 'ajustement',
            'Variation stock du ' . Carbon::parse($date)->format('d-m-Y'), $total, $lignes
        );
    }

    private function creerJournalBrouillonSession(PointDeVente $pointDeVente, string $date, string $session, int $userId, string $type, string $libelle, float $montant, array $lignes): ?JournalComptable
    {
        $montant = round($montant, 2);
        if ($montant <= 0 || empty($lignes)) {
            Log::info('[Comptabilité Session] Aucun journal créé : total nul ou aucune ligne', [
                'point_de_vente_id' => $pointDeVente->id,
                'session' => $session,
                'type' => $type,
                'montant' => $montant,
                'nombre_lignes' => count($lignes),
            ]);
            return null;
        }

        $journal = JournalComptable::create([
            'date_ecriture' => $date,
            'numero_piece' => JournalComptable::genererNumeroPiece($type, $pointDeVente->entreprise_id, Carbon::parse($date)),
            'libelle' => $libelle,
            'montant_total' => $montant,
            'entreprise_id' => $pointDeVente->entreprise_id,
            'point_de_vente_id' => $pointDeVente->id,
            'session' => $session,
            'user_id' => $userId,
            'type_operation' => $type,
            'statut' => 'brouillon',
        ]);

        foreach ($lignes as $index => $ligne) {
            EcritureComptable::create($ligne + [
                'journal_id' => $journal->id,
                'ordre' => $index + 1,
            ]);
        }

        if (!$journal->fresh('ecritures')->estEquilibre()) {
            throw new \RuntimeException("Le journal {$journal->numero_piece} n'est pas équilibré.");
        }

        Log::info('[Comptabilité Session] Brouillon équilibré créé', [
            'journal_id' => $journal->id,
            'numero_piece' => $journal->numero_piece,
            'type' => $type,
            'session' => $session,
            'montant' => $montant,
            'nombre_lignes' => count($lignes),
            'statut' => $journal->statut,
        ]);

        return $journal;
    }

    private function journalSessionExistant(PointDeVente $pointDeVente, string $date, string $session, string $type): ?JournalComptable
    {
        return JournalComptable::where('point_de_vente_id', $pointDeVente->id)
            ->where('date_ecriture', $date)
            ->where('session', $session)
            ->where('type_operation', $type)
            ->first();
    }

    private function compteClientSession(PointDeVente $pointDeVente): Compte
    {
        $modeCompteClient = ModePaiement::where('entreprise_id', $pointDeVente->entreprise_id)
            ->where('code', 'compte_client')
            ->first();
        $compteId = $modeCompteClient?->compte_id ?? $pointDeVente->compte_client_id;

        return $this->comptePourClasse(
            $compteId,
            $pointDeVente->entreprise_id,
            '4',
            'compte client du point de vente'
        );
    }

    private function comptePourClasse(?int $compteId, int $entrepriseId, string $classePrefixe, string $libelle): Compte
    {
        $compte = $compteId ? Compte::with('classeComptable')->find($compteId) : null;
        if (
            !$compte
            || (int) $compte->entreprise_id !== $entrepriseId
            || !$compte->classeComptable
            || !str_starts_with((string) $compte->classeComptable->numero, $classePrefixe)
        ) {
            throw new \RuntimeException("Configurez {$libelle} avec un compte de classe {$classePrefixe} avant de fermer la session.");
        }

        return $compte;
    }

    private function normaliserMode(?string $mode): string
    {
        return strtolower(str_replace([' ', '-', 'é', 'è', 'ê', 'à'], ['_', '_', 'e', 'e', 'e', 'a'], trim((string) $mode)));
    }

    private function dateLibelleSession(string $date): string
    {
        return Carbon::parse($date)->locale('fr')->translatedFormat('l d-m-Y');
    }

    private function libellePaiementSession(ModePaiement $mode, string $date): string
    {
        $code = $this->normaliserMode($mode->code);
        $nom = $this->normaliserMode($mode->nom);

        if (str_starts_with($code, 'offre') || str_starts_with($nom, 'offre')) {
            return 'Offre du ' . $date;
        }

        if ($code === 'especes' || $nom === 'especes') {
            return 'Règlement factures clients par ' . $mode->nom . ' du ' . $date;
        }

        return 'Règlement facture par ' . $mode->nom . ' du ' . $date;
    }

    /**
     * Enregistre automatiquement une vente dans le journal comptable
     */
    public function enregistrerVente(Commande $commande)
    {
        if (!$commande->panier || !$commande->panier->pointDeVente) {
            throw new \Exception('Impossible d\'enregistrer la vente : panier ou point de vente manquant');
        }

        $pointDeVente = $commande->panier->pointDeVente;
        $entreprise = $pointDeVente->entreprise;

        // Vérifier si la comptabilité est active pour ce point de vente
        if (!$pointDeVente->comptabilite_active) {
            return null;
        }

        $montantTotal = $this->calculerMontantCommande($commande);

        return DB::transaction(function () use ($commande, $pointDeVente, $entreprise, $montantTotal) {
            // Convertir created_at en Carbon si nécessaire
            $dateCreation = $commande->created_at instanceof \Carbon\Carbon ? 
                          $commande->created_at : 
                          \Carbon\Carbon::parse($commande->created_at);
            
            // Créer l'entrée journal
            $journal = JournalComptable::create([
                'date_ecriture' => $dateCreation->toDateString(),
                'numero_piece' => JournalComptable::genererNumeroPiece('vente', $entreprise->id, $dateCreation),
                'libelle' => "Vente {$pointDeVente->nom} - Table " . ($commande->panier->tableResto->numero ?? 'N/A'),
                'montant_total' => $montantTotal,
                'entreprise_id' => $entreprise->id,
                'point_de_vente_id' => $pointDeVente->id,
                'commande_id' => $commande->id,
                'panier_id' => $commande->panier_id,
                'user_id' => \Illuminate\Support\Facades\Auth::id() ?? $commande->panier->opened_by,
                'type_operation' => 'vente',
                'statut' => 'valide'
            ]);

            // Déterminer les comptes selon le mode de paiement
            $comptesVente = $this->obtenirComptesVente($commande, $pointDeVente);

            // Écriture de débit (entrée d'argent)
            EcritureComptable::create([
                'journal_id' => $journal->id,
                'compte_id' => $comptesVente['debit']->id,
                'libelle' => $comptesVente['debit_libelle'],
                'debit' => $montantTotal,
                'credit' => 0,
                'client_id' => $commande->panier->client_id,
                'ordre' => 1
            ]);

            // Écriture de crédit (vente)
            EcritureComptable::create([
                'journal_id' => $journal->id,
                'compte_id' => $comptesVente['credit']->id,
                'libelle' => $comptesVente['credit_libelle'],
                'debit' => 0,
                'credit' => $montantTotal,
                'ordre' => 2
            ]);

            Log::info('Vente enregistrée en comptabilité', [
                'journal_id' => $journal->id,
                'commande_id' => $commande->id,
                'montant' => $montantTotal
            ]);

            return $journal;
        });
    }

    /**
     * Enregistre un paiement de créance
     */
    public function enregistrerPaiementCreance(Paiement $paiement)
    {
        $commande = $paiement->commande;
        $pointDeVente = $commande->panier->pointDeVente;
        $entreprise = $pointDeVente->entreprise;

        if (!$pointDeVente->comptabilite_active) {
            return null;
        }

        return DB::transaction(function () use ($paiement, $commande, $pointDeVente, $entreprise) {
            $journal = JournalComptable::create([
                'date_ecriture' => $paiement->date_paiement,
                'numero_piece' => JournalComptable::genererNumeroPiece('paiement', $entreprise->id, $paiement->created_at),
                'libelle' => "Règlement créance - " . ($commande->panier->client->nom ?? 'Client'),
                'montant_total' => $paiement->montant,
                'entreprise_id' => $entreprise->id,
                'point_de_vente_id' => $pointDeVente->id,
                'commande_id' => $commande->id,
                'paiement_id' => $paiement->id,
                'user_id' => $paiement->user_id,
                'type_operation' => 'paiement',
                'statut' => 'valide'
            ]);

            // Débit : Encaissement dans la caisse du point de vente
            $compteCaisse = $pointDeVente->compte_caisse_id ? 
                Compte::find($pointDeVente->compte_caisse_id) : 
                $this->obtenirCompteCaisseDefaut($entreprise);
                
            Log::info('Compte caisse utilisé pour paiement créance', [
                'compte_id' => $compteCaisse->id,
                'compte_nom' => $compteCaisse->nom,
                'point_de_vente' => $pointDeVente->nom,
                'montant' => $paiement->montant
            ]);

            EcritureComptable::create([
                'journal_id' => $journal->id,
                'compte_id' => $compteCaisse->id,
                'libelle' => "Encaissement créance - {$paiement->mode}",
                'debit' => $paiement->montant,
                'credit' => 0,
                'client_id' => $commande->panier->client_id,
                'ordre' => 1
            ]);

            // Crédit : Diminution créance client
            $compteClient = $pointDeVente->compte_client_id ? 
                Compte::find($pointDeVente->compte_client_id) : 
                $this->obtenirCompteClientDefaut($entreprise);

            EcritureComptable::create([
                'journal_id' => $journal->id,
                'compte_id' => $compteClient->id,
                'libelle' => "Règlement créance - " . ($commande->panier->client->nom ?? 'Client'),
                'debit' => 0,
                'credit' => $paiement->montant,
                'client_id' => $commande->panier->client_id,
                'ordre' => 2
            ]);

            Log::info('Paiement créance enregistré en comptabilité', [
                'journal_id' => $journal->id,
                'paiement_id' => $paiement->id,
                'compte_caisse' => $compteCaisse->nom,
                'compte_client' => $compteClient->nom,
                'montant' => $paiement->montant
            ]);

            return $journal;
        });
    }

    /**
     * Enregistre une dépense/entrée-sortie
     */
    public function enregistrerMouvement(EntreeSortie $mouvement)
    {
        $compte = $mouvement->compte;
        $entreprise = $compte->entreprise;

        return DB::transaction(function () use ($mouvement, $compte, $entreprise) {
            $journal = JournalComptable::create([
                'date_ecriture' => $mouvement->created_at->toDateString(),
                'numero_piece' => JournalComptable::genererNumeroPiece('mouvement', $entreprise->id, $mouvement->created_at),
                'libelle' => $mouvement->libele,
                'montant_total' => $mouvement->montant,
                'entreprise_id' => $entreprise->id,
                'point_de_vente_id' => $mouvement->point_de_vente_id,
                'user_id' => $mouvement->user_id,
                'type_operation' => $mouvement->type === 'entree' ? 'recette' : 'depense',
                'statut' => 'brouillon'
            ]);

            // Écriture selon le type (entrée/sortie)
            if ($mouvement->type === 'entree') {
                $pointDeVente = $mouvement->pointDeVente;
                $compteCaisse = $pointDeVente?->compteCaisse;
                if (!$compteCaisse || (int) $compteCaisse->entreprise_id !== (int) $entreprise->id) {
                    throw new \RuntimeException('Configurez un compte caisse valide pour ce point de vente avant d’enregistrer une entrée.');
                }

                // Entrée : débit de la caisse et crédit du compte sélectionné.
                EcritureComptable::create([
                    'journal_id' => $journal->id,
                    'compte_id' => $compteCaisse->id,
                    'libelle' => 'Entrée caisse - ' . $mouvement->libele,
                    'debit' => $mouvement->montant,
                    'credit' => 0,
                    'ordre' => 1
                ]);

                EcritureComptable::create([
                    'journal_id' => $journal->id,
                    'compte_id' => $compte->id,
                    'libelle' => $mouvement->libele,
                    'debit' => 0,
                    'credit' => $mouvement->montant,
                    'ordre' => 2
                ]);
            } else {
                // Sortie : Débit du compte de charge (nature de la dépense)
                EcritureComptable::create([
                    'journal_id' => $journal->id,
                    'compte_id' => $compte->id,
                    'libelle' => $mouvement->libele,
                    'debit' => $mouvement->montant,
                    'credit' => 0,
                    'ordre' => 1
                ]);

                // Crédit : Sortie de la caisse du point de vente (l'argent sort de la caisse)
                $compteCaisse = $this->obtenirCompteCaissePointDeVente($mouvement->point_de_vente_id, $entreprise);
                EcritureComptable::create([
                    'journal_id' => $journal->id,
                    'compte_id' => $compteCaisse->id,
                    'libelle' => "Sortie caisse - " . $mouvement->libele,
                    'debit' => 0,
                    'credit' => $mouvement->montant,
                    'ordre' => 2
                ]);
            }

            // Marquer le mouvement comme comptabilisé
            $mouvement->update([
                'journal_id' => $journal->id,
                'comptabilise' => true
            ]);

            return $journal;
        });
    }

    /**
     * Enregistre une écriture comptable manuelle entre comptes
     */
    public function enregistrerTransfert($compteSource, $compteDestination, $montant, $libelle, $entrepriseId, $userId = null, $reference = null, $typeOperation = 'vente', $dateHeureEcriture = null)
    {
        return DB::transaction(function () use ($compteSource, $compteDestination, $montant, $libelle, $entrepriseId, $userId, $reference, $typeOperation, $dateHeureEcriture) {
            $typeOperation = in_array($typeOperation, ['vente', 'achat', 'od', 'caisse'], true)
                ? $typeOperation
                : 'vente';

            $dateHeure = $dateHeureEcriture instanceof \Carbon\Carbon
                ? $dateHeureEcriture
                : now();

            $journal = JournalComptable::create([
                'date_ecriture' => $dateHeure->toDateString(),
                'heure_ecriture' => $dateHeure->format('H:i:s'),
                'numero_piece' => $reference ?? JournalComptable::genererNumeroPiece($typeOperation, $entrepriseId, $dateHeure),
                'libelle' => $libelle,
                'montant_total' => $montant,
                'entreprise_id' => $entrepriseId,
                'point_de_vente_id' => null,
                'user_id' => $userId ?? \Illuminate\Support\Facades\Auth::id(),
                'type_operation' => $typeOperation,
                'statut' => 'brouillon',
                'created_at' => $dateHeure,
                'updated_at' => $dateHeure
            ]);

            EcritureComptable::create([
                'journal_id' => $journal->id,
                'compte_id' => $compteSource->id,
                'libelle' => "Débit - {$libelle}",
                'debit' => $montant,
                'credit' => 0,
                'ordre' => 1
            ]);

            EcritureComptable::create([
                'journal_id' => $journal->id,
                'compte_id' => $compteDestination->id,
                'libelle' => "Crédit - {$libelle}",
                'debit' => 0,
                'credit' => $montant,
                'ordre' => 2
            ]);

            Log::info('Écriture comptable enregistrée', [
                'journal_id' => $journal->id,
                'type_operation' => $typeOperation,
                'source' => $compteSource->nom,
                'destination' => $compteDestination->nom,
                'montant' => $montant
            ]);

            return $journal;
        });
    }

    /**
     * Calcule le montant total d'une commande
     */
    private function calculerMontantCommande(Commande $commande)
    {
        // Charger les produits du panier si pas déjà fait
        if (!$commande->panier) {
            $commande->load('panier');
        }
        
        if (!$commande->panier->produits) {
            $commande->panier->load('produits');
        }

        if ($commande->panier && $commande->panier->produits) {
            $montant = $commande->panier->produits->sum(function($produit) {
                return $produit->pivot->quantite * $produit->prix_vente;
            });
            
            Log::debug('Montant calculé pour commande', [
                'commande_id' => $commande->id,
                'panier_id' => $commande->panier_id,
                'nb_produits' => $commande->panier->produits->count(),
                'montant_calcule' => $montant
            ]);
            
            return $montant;
        }

        Log::warning('Impossible de calculer le montant de la commande', [
            'commande_id' => $commande->id,
            'panier_id' => $commande->panier_id
        ]);

        return 0;
    }

    /**
     * Détermine les comptes pour une vente selon le mode de paiement
     */
    private function obtenirComptesVente(Commande $commande, $pointDeVente)
    {
        $entreprise = $pointDeVente->entreprise;
        
        $modePaiement = strtolower(str_replace([' ', '-', 'é', 'è', 'ê', 'à'], ['_', '_', 'e', 'e', 'e', 'a'], (string) $commande->mode_paiement));

        switch ($modePaiement) {
            case 'especes':
            case 'espèces':
            case 'cash':
                $compteDebit = $pointDeVente->compte_caisse_id ? 
                    Compte::find($pointDeVente->compte_caisse_id) : 
                    $this->obtenirCompteCaisseDefaut($entreprise);
                $debitLibelle = 'Vente au comptant';
                break;
                
            case 'compte_client':
            case 'credit':
                $compteDebit = $pointDeVente->compte_client_id ? 
                    Compte::find($pointDeVente->compte_client_id) : 
                    $this->obtenirCompteClientDefaut($entreprise);
                $debitLibelle = 'Vente à crédit';
                break;
                
            case 'mobile_money':
                $compteDebit = $this->obtenirCompteMobileMoneyDefaut($entreprise);
                $debitLibelle = 'Vente mobile money';
                break;
                
            default:
                $compteDebit = $pointDeVente->compte_caisse_id ? 
                    Compte::find($pointDeVente->compte_caisse_id) : 
                    $this->obtenirCompteCaisseDefaut($entreprise);
                $debitLibelle = 'Vente';
        }

        $compteCredit = $pointDeVente->compte_vente_id ? 
            Compte::find($pointDeVente->compte_vente_id) : 
            $this->obtenirCompteVenteDefaut($entreprise);

        return [
            'debit' => $compteDebit,
            'debit_libelle' => $debitLibelle,
            'credit' => $compteCredit,
            'credit_libelle' => 'Vente marchandises'
        ];
    }

    /**
     * Obtient ou crée les comptes par défaut
     */
    private function obtenirCompteCaisseDefaut($entreprise)
    {
        return Compte::firstOrCreate(
            ['numero' => '531', 'entreprise_id' => $entreprise->id],
            [
                'nom' => 'Caisse',
                'type' => 'actif',
                'classe_comptable' => '5',
                'sous_classe' => '531',
                'description' => 'Caisse - Espèces'
            ]
        );
    }

    private function obtenirCompteVenteDefaut($entreprise)
    {
        return Compte::firstOrCreate(
            ['numero' => '701', 'entreprise_id' => $entreprise->id],
            [
                'nom' => 'Ventes de marchandises',
                'type' => 'passif',
                'classe_comptable' => '7',
                'sous_classe' => '701',
                'description' => 'Chiffre d\'affaires - Ventes'
            ]
        );
    }

    private function obtenirCompteClientDefaut($entreprise)
    {
        return Compte::firstOrCreate(
            ['numero' => '411', 'entreprise_id' => $entreprise->id],
            [
                'nom' => 'Clients',
                'type' => 'actif',
                'classe_comptable' => '4',
                'sous_classe' => '411',
                'est_collectif' => true,
                'description' => 'Créances clients'
            ]
        );
    }

    private function obtenirCompteMobileMoneyDefaut($entreprise)
    {
        return Compte::firstOrCreate(
            ['numero' => '532', 'entreprise_id' => $entreprise->id],
            [
                'nom' => 'Banque mobile money',
                'type' => 'actif',
                'classe_comptable' => '5',
                'sous_classe' => '532',
                'description' => 'Comptes mobile money'
            ]
        );
    }

    private function obtenirCompteEncaissement($mode, $pointDeVente)
    {
        $entreprise = $pointDeVente->entreprise;
        
        switch (strtolower($mode)) {
            case 'espèces':
            case 'cash':
                return $this->obtenirCompteCaisseDefaut($entreprise);
            case 'mobile_money':
                return $this->obtenirCompteMobileMoneyDefaut($entreprise);
            default:
                return $this->obtenirCompteCaisseDefaut($entreprise);
        }
    }

    private function obtenirCompteContrepartie($compte, $type, $entreprise)
    {
        // Logique simplifiée pour MVP
        if ($type === 'recette') {
            return $this->obtenirCompteVenteDefaut($entreprise);
        }
        
        return $compte; // Pour l'instant, on retourne le même compte
    }

    private function obtenirCompteCharge($libelle, $entreprise)
    {
        // Analyse du libellé pour déterminer le type de charge
        $libelle_lower = strtolower($libelle);
        
        if (str_contains($libelle_lower, 'achat') || str_contains($libelle_lower, 'stock')) {
            $numero = '601';
            $nom = 'Achats de marchandises';
        } elseif (str_contains($libelle_lower, 'transport') || str_contains($libelle_lower, 'livraison')) {
            $numero = '624';
            $nom = 'Transports';
        } elseif (str_contains($libelle_lower, 'électricité') || str_contains($libelle_lower, 'eau')) {
            $numero = '628';
            $nom = 'Charges d\'exploitation diverses';
        } else {
            $numero = '622';
            $nom = 'Charges externes diverses';
        }

        return Compte::firstOrCreate(
            ['numero' => $numero, 'entreprise_id' => $entreprise->id],
            [
                'nom' => $nom,
                'type' => 'passif',
                'classe_comptable' => '6',
                'sous_classe' => $numero,
                'description' => 'Compte de charges'
            ]
        );
    }

    private function obtenirCompteCaissePointDeVente($pointDeVenteId, $entreprise)
    {
        // Récupérer le point de vente pour accéder à son compte caisse configuré
        if ($pointDeVenteId) {
            $pointDeVente = \App\Models\PointDeVente::find($pointDeVenteId);
            
            // Si le point de vente a un compte caisse configuré, l'utiliser
            if ($pointDeVente && $pointDeVente->compte_caisse_id) {
                $compteCaisse = Compte::find($pointDeVente->compte_caisse_id);
                if ($compteCaisse) {
                    Log::info('Utilisation du compte caisse du point de vente', [
                        'point_de_vente' => $pointDeVente->nom,
                        'compte_caisse' => $compteCaisse->nom,
                        'compte_id' => $compteCaisse->id
                    ]);
                    return $compteCaisse;
                }
            }
        }
        
        // Sinon, utiliser le compte caisse par défaut
        $compteDefaut = $this->obtenirCompteCaisseDefaut($entreprise);
        Log::info('Utilisation du compte caisse par défaut', [
            'compte_caisse' => $compteDefaut->nom,
            'compte_id' => $compteDefaut->id
        ]);
        return $compteDefaut;
    }
}
