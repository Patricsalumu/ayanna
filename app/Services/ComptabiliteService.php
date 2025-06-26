<?php

namespace App\Services;

use App\Models\JournalComptable;
use App\Models\EcritureComptable;
use App\Models\Compte;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\EntreeSortie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComptabiliteService
{
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
            // Créer l'entrée journal
            $journal = JournalComptable::create([
                'date_ecriture' => $commande->created_at->toDateString(),
                'numero_piece' => JournalComptable::genererNumeroPiece('vente', $entreprise->id, $commande->created_at),
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
                'type_operation' => $mouvement->type === 'credit' ? 'recette' : 'depense',
                'statut' => 'valide'
            ]);

            // Écriture selon le type (crédit/débit)
            if ($mouvement->type === 'credit') {
                // Recette : Débit du compte concerné (entrée d'argent)
                EcritureComptable::create([
                    'journal_id' => $journal->id,
                    'compte_id' => $compte->id,
                    'libelle' => $mouvement->libele,
                    'debit' => $mouvement->montant,
                    'credit' => 0,
                    'ordre' => 1
                ]);

                // Crédit : Compte de contrepartie 
                $compteContrepartie = $this->obtenirCompteContrepartie($compte, 'recette', $entreprise);
                EcritureComptable::create([
                    'journal_id' => $journal->id,
                    'compte_id' => $compteContrepartie->id,
                    'libelle' => "Contrepartie " . $mouvement->libele,
                    'debit' => 0,
                    'credit' => $mouvement->montant,
                    'ordre' => 2
                ]);
            } else {
                // Dépense : Débit du compte de charge (nature de la dépense)
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
     * Enregistre un transfert entre comptes
     */
    public function enregistrerTransfert($compteSource, $compteDestination, $montant, $libelle, $entrepriseId, $userId = null, $reference = null)
    {
        return DB::transaction(function () use ($compteSource, $compteDestination, $montant, $libelle, $entrepriseId, $userId, $reference) {
            // Créer l'entrée journal
            $journal = JournalComptable::create([
                'date_ecriture' => now()->toDateString(),
                'numero_piece' => $reference ?? JournalComptable::genererNumeroPiece('transfert', $entrepriseId, now()),
                'libelle' => $libelle,
                'montant_total' => $montant,
                'entreprise_id' => $entrepriseId,
                'point_de_vente_id' => null, // Transfert global
                'user_id' => $userId ?? \Illuminate\Support\Facades\Auth::id(),
                'type_operation' => 'transfert',
                'statut' => 'valide'
            ]);

            // Écriture de débit (compte de destination - reçoit l'argent)
            EcritureComptable::create([
                'journal_id' => $journal->id,
                'compte_id' => $compteDestination->id,
                'libelle' => "Transfert reçu de {$compteSource->nom}",
                'debit' => $montant,
                'credit' => 0,
                'ordre' => 1
            ]);

            // Écriture de crédit (compte source - donne l'argent)
            EcritureComptable::create([
                'journal_id' => $journal->id,
                'compte_id' => $compteSource->id,
                'libelle' => "Transfert vers {$compteDestination->nom}",
                'debit' => 0,
                'credit' => $montant,
                'ordre' => 2
            ]);

            Log::info('Transfert enregistré en comptabilité', [
                'journal_id' => $journal->id,
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
        if ($commande->montant) {
            return $commande->montant;
        }

        if ($commande->panier && $commande->panier->produits) {
            return $commande->panier->produits->sum(function($produit) {
                return $produit->pivot->quantite * $produit->prix_vente;
            });
        }

        return 0;
    }

    /**
     * Détermine les comptes pour une vente selon le mode de paiement
     */
    private function obtenirComptesVente(Commande $commande, $pointDeVente)
    {
        $entreprise = $pointDeVente->entreprise;
        
        switch (strtolower($commande->mode_paiement)) {
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
