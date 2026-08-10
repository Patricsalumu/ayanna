<?php

namespace App\Http\Controllers;

use App\Models\JournalComptable;
use App\Models\EcritureComptable;
use App\Models\Compte;
use App\Services\ComptabiliteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComptabiliteController extends Controller
{
    protected $comptabiliteService;

    public function __construct(ComptabiliteService $comptabiliteService)
    {
        $this->comptabiliteService = $comptabiliteService;
    }

    /**
     * Journal comptable - Liste des écritures
     */
    public function journal(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $dateDebut = $request->get('date_debut', now()->startOfMonth()->toDateString());
        $dateFin = $request->get('date_fin', now()->toDateString());
        $pointDeVenteId = $request->get('point_de_vente_id');
        $typeOperation = $request->get('type_operation');

        $query = JournalComptable::with(['pointDeVente', 'user', 'ecritures.compte'])
            ->parEntreprise($entrepriseId)
            ->parPeriode($dateDebut, $dateFin)
            ->orderByDesc('date_ecriture')
            ->orderByDesc('created_at');

        if ($pointDeVenteId) {
            $query->parPointDeVente($pointDeVenteId);
        }

        if ($typeOperation) {
            $query->parType($typeOperation);
        }

        $journaux = $query->paginate(50);
        $pointsDeVente = \App\Models\PointDeVente::where('entreprise_id', $entrepriseId)->get();

        return view('comptabilite.journal', compact('journaux', 'pointsDeVente', 'dateDebut', 'dateFin', 'pointDeVenteId', 'typeOperation'));
    }

    public function validerJournal(JournalComptable $journal)
    {
        if ($journal->statut !== 'brouillon') {
            return back()->with('error', 'Seules les écritures en brouillon peuvent être validées.');
        }

        $journal->update(['statut' => 'valide']);

        return back()->with('success', 'L’écriture a été validée avec succès.');
    }

    public function annulerJournal(JournalComptable $journal)
    {
        if ($journal->statut !== 'brouillon') {
            return back()->with('error', 'Seules les écritures en brouillon peuvent être annulées.');
        }

        $journal->update(['statut' => 'annule']);

        return back()->with('success', 'L’écriture a été annulée et grisée sans création d’écriture inverse.');
    }

    private function appliquerFiltreJournauxNonAnnes(Builder|HasMany $query, $dateDebut = null, $dateFin = null)
    {
        return $query->whereHas('journal', function ($j) use ($dateDebut, $dateFin) {
            if ($dateDebut !== null && $dateFin !== null) {
                $j->whereBetween('date_ecriture', [$dateDebut, $dateFin]);
            } elseif ($dateDebut !== null) {
                $j->where('date_ecriture', '>=', $dateDebut);
            } elseif ($dateFin !== null) {
                $j->where('date_ecriture', '<=', $dateFin);
            }

            $j->where('statut', '!=', 'annule');
        });
    }

    /**
     * Grand livre - Mouvements par compte
     */
    public function grandLivre(Request $request, $compteId = null)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $dateDebut = $request->get('date_debut', now()->startOfMonth()->toDateString());
        $dateFin = $request->get('date_fin', now()->toDateString());
        $search = trim($request->get('search', ''));
        
        $queryComptes = Compte::where('entreprise_id', $entrepriseId);

        if (!empty($search)) {
            $queryComptes->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhere('nom', 'like', "%{$search}%");
            });
        }

        $comptes = $queryComptes->orderBy('numero')->get();
        
        if ($compteId) {
            $compte = Compte::with('classeComptable')->findOrFail($compteId);
            
            $ecritures = EcritureComptable::with(['journal', 'client', 'produit'])
                ->parCompte($compteId)
                ->when(true, function ($query) use ($dateDebut, $dateFin) {
                    return $this->appliquerFiltreJournauxNonAnnes($query, $dateDebut, $dateFin);
                })
                ->orderBy('created_at')
                ->get();

            if (!empty($search)) {
                $rechercheMin = strtolower($search);
                $ecritures = $ecritures->filter(function ($ecriture) use ($rechercheMin) {
                    $libelle = strtolower($ecriture->libelle_ecriture ?: ($ecriture->journal->libelle ?? ''));
                    $reference = strtolower($ecriture->journal->reference ?? '');
                    $numeroCompte = strtolower($ecriture->compte->numero ?? '');
                    $nomCompte = strtolower($ecriture->compte->nom ?? '');

                    return str_contains($libelle, $rechercheMin)
                        || str_contains($reference, $rechercheMin)
                        || str_contains($numeroCompte, $rechercheMin)
                        || str_contains($nomCompte, $rechercheMin);
                })->values();
            }

            // Calcul du solde initial
            $soldeInitial = $compte->solde_initial;
            $mouvementsAnterieurs = EcritureComptable::parCompte($compteId)
                ->whereHas('journal', function ($q) use ($dateDebut) {
                    $q->where('date_ecriture', '<', $dateDebut)
                      ->where('statut', '!=', 'annule');
                })
                ->get();

            foreach ($mouvementsAnterieurs as $mvt) {
                if ($compte->type === 'actif') {
                    $soldeInitial += $mvt->debit - $mvt->credit;
                } else {
                    $soldeInitial += $mvt->credit - $mvt->debit;
                }
            }

            return view('comptabilite.grand-livre-detail', compact('compte', 'ecritures', 'soldeInitial', 'dateDebut', 'dateFin', 'comptes'));
        }

        return view('comptabilite.grand-livre', compact('comptes', 'dateDebut', 'dateFin', 'search'));
    }

    /**
     * Balance comptable
     */
    public function balance(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $date = $request->get('date', now()->toDateString());
        
        $comptes = Compte::where('entreprise_id', $entrepriseId)
            ->with(['ecritures' => function($q) use ($date) {
                $q->whereHas('journal', function($j) use ($date) {
                    $j->where('date_ecriture', '<=', $date)
                      ->where('statut', '!=', 'annule');
                });
            }])
            ->orderBy('numero')
            ->get();

        $balance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($comptes as $compte) {
            $debitPeriode = $compte->ecritures->sum('debit');
            $creditPeriode = $compte->ecritures->sum('credit');
            
            if ($compte->type === 'actif') {
                $solde = $compte->solde_initial + $debitPeriode - $creditPeriode;
                $soldeDebit = $solde > 0 ? $solde : 0;
                $soldeCredit = $solde < 0 ? abs($solde) : 0;
            } else {
                $solde = $compte->solde_initial + $creditPeriode - $debitPeriode;
                $soldeCredit = $solde > 0 ? $solde : 0;
                $soldeDebit = $solde < 0 ? abs($solde) : 0;
            }

            $balance[] = [
                'compte' => $compte,
                'debit_periode' => $debitPeriode,
                'credit_periode' => $creditPeriode,
                'solde_debit' => $soldeDebit,
                'solde_credit' => $soldeCredit
            ];

            $totalDebit += $soldeDebit;
            $totalCredit += $soldeCredit;
        }

        return view('comptabilite.balance', compact('balance', 'totalDebit', 'totalCredit', 'date'));
    }

    /**
     * Bilan comptable simplifié
     */
    public function bilan(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $date = $request->get('date', now()->toDateString());
        
        // Calcul personnalisé par classe comptable selon les règles fournies :
        // Classes débiteurs (2,3,4,5): formule solde = solde_initial + debit - credit
        // Classe 1 (créditeur): formule solde = solde_initial + credit - debit
        // Si le solde est négatif par rapport à la nature, on le bascule de côté (actif <-> passif)
        $comptes = Compte::where('entreprise_id', $entrepriseId)
            ->whereHas('classeComptable', function($q) {
                $q->whereNotIn('numero', [6, 7]);
            })
            ->with(['classeComptable', 'ecritures' => function($q) use ($date) {
                return $this->appliquerFiltreJournauxNonAnnes($q, null, $date);
            }])
            ->orderBy('numero')
            ->get();

        $actifs = [];
        $passifs = [];
        $totalActif = 0;
        $totalPassif = 0;

        foreach ($comptes as $compte) {
            $classeNum = intval($compte->classeComptable->numero ?? 0);

            // Solde initial auquel on ajoute les mouvements validés jusqu'à la date
            $soldeInitial = $compte->solde_initial;
            $debit = $compte->ecritures->sum('debit');
            $credit = $compte->ecritures->sum('credit');

            // Déterminer formule selon la classe
            $isDebiteurClass = in_array($classeNum, [2,3,4,5,6]); // 6 (charges) traité ici comme débiteur pour solde
            $isCrediteurClass = in_array($classeNum, [1,7]);

            if ($isDebiteurClass) {
                $solde = $soldeInitial + $debit - $credit; // débiteur: débit - crédit
            } elseif ($isCrediteurClass) {
                $solde = $soldeInitial + $credit - $debit; // créditeur: crédit - débit
            } else {
                // Par défaut, traiter comme débiteur
                $solde = $soldeInitial + $debit - $credit;
                $isDebiteurClass = true;
            }

            // Attribuer côté actif ou passif en fonction du signe et de la nature
            if ($isDebiteurClass) {
                if ($solde >= 0) {
                    // reste à l'actif
                    $compte->solde_debit = $solde;
                    $compte->solde_credit = 0;
                    $actifs[] = $compte;
                    $totalActif += $solde;
                } else {
                    // devient passif (valeur positive côté crédit)
                    $compte->solde_debit = 0;
                    $compte->solde_credit = abs($solde);
                    $passifs[] = $compte;
                    $totalPassif += abs($solde);
                }
            } else {
                // créditeur
                if ($solde >= 0) {
                    // reste au passif (crédit)
                    $compte->solde_debit = 0;
                    $compte->solde_credit = $solde;
                    $passifs[] = $compte;
                    $totalPassif += $solde;
                } else {
                    // devient actif (valeur positive côté débit)
                    $compte->solde_debit = abs($solde);
                    $compte->solde_credit = 0;
                    $actifs[] = $compte;
                    $totalActif += abs($solde);
                }
            }
        }

        // Calcul du résultat de l'exercice (à ajouter au passif)
        $resultatExercice = $this->calculerResultatExercice($entrepriseId, $date);
        
        // Si bénéfice, on l'ajoute au passif
        if ($resultatExercice > 0) {
            $totalPassif += $resultatExercice;
        } else if ($resultatExercice < 0) {
            // Si perte, on l'ajoute à l'actif (en valeur absolue)
            $totalActif += abs($resultatExercice);
        }

        return view('comptabilite.bilan', compact('actifs', 'passifs', 'totalActif', 'totalPassif', 'date', 'resultatExercice'));
    }

    /**
     * Construit le bilan selon les règles de classe comptable.
     */
    private function construireBilan($entrepriseId, $date)
    {
        $comptes = Compte::where('entreprise_id', $entrepriseId)
            ->whereHas('classeComptable', function($q) {
                $q->whereNotIn('numero', [6, 7]);
            })
            ->with(['classeComptable', 'ecritures' => function($q) use ($date) {
                return $this->appliquerFiltreJournauxNonAnnes($q, null, $date);
            }])
            ->orderBy('numero')
            ->get();

        $actifs = [];
        $passifs = [];
        $totalActif = 0;
        $totalPassif = 0;

        foreach ($comptes as $compte) {
            $classeNum = intval($compte->classeComptable->numero ?? 0);
            $isDebiteurClass = in_array($classeNum, [2,3,4,5,6]);
            $isCrediteurClass = in_array($classeNum, [1,7]);

            $soldeInitial = $compte->solde_initial;
            $debit = $compte->ecritures->sum('debit');
            $credit = $compte->ecritures->sum('credit');

            if ($isDebiteurClass) {
                $solde = $soldeInitial + $debit - $credit;
            } elseif ($isCrediteurClass) {
                $solde = $soldeInitial + $credit - $debit;
            } else {
                $solde = $soldeInitial + $debit - $credit;
                $isDebiteurClass = true;
            }

            if ($isDebiteurClass) {
                if ($solde >= 0) {
                    $compte->solde_debit = $solde;
                    $compte->solde_credit = 0;
                    $compte->solde_bilan = $solde;
                    $actifs[] = $compte;
                    $totalActif += $solde;
                } else {
                    $compte->solde_debit = 0;
                    $compte->solde_credit = abs($solde);
                    $compte->solde_bilan = abs($solde);
                    $passifs[] = $compte;
                    $totalPassif += abs($solde);
                }
            } else {
                if ($solde >= 0) {
                    $compte->solde_debit = 0;
                    $compte->solde_credit = $solde;
                    $compte->solde_bilan = $solde;
                    $passifs[] = $compte;
                    $totalPassif += $solde;
                } else {
                    $compte->solde_debit = abs($solde);
                    $compte->solde_credit = 0;
                    $compte->solde_bilan = abs($solde);
                    $actifs[] = $compte;
                    $totalActif += abs($solde);
                }
            }
        }

        return [
            'actifs' => $actifs,
            'passifs' => $passifs,
            'totalActif' => $totalActif,
            'totalPassif' => $totalPassif,
        ];
    }

    /**
     * Calcule le résultat de l'exercice pour le bilan
     */
    private function calculerResultatExercice($entrepriseId, $date)
    {
        // Produits (classe 7)
        $produits = Compte::where('entreprise_id', $entrepriseId)
            ->whereHas('classeComptable', function($q) {
                $q->where('numero', '7');
            })
            ->with(['ecritures' => function($q) use ($date) {
                $q->whereHas('journal', function($j) use ($date) {
                    $j->where('date_ecriture', '<=', $date)
                      ->where('statut', '!=', 'annule');
                });
            }])
            ->get();

        // Charges (classe 6)
        $charges = Compte::where('entreprise_id', $entrepriseId)
            ->whereHas('classeComptable', function($q) {
                $q->where('numero', '6');
            })
            ->with(['ecritures' => function($q) use ($date) {
                $q->whereHas('journal', function($j) use ($date) {
                    $j->where('date_ecriture', '<=', $date)
                      ->where('statut', '!=', 'annule');
                });
            }])
            ->get();

        $totalProduits = 0;
        $totalCharges = 0;

        foreach ($produits as $compte) {
            $credit = $compte->ecritures->sum('credit');
            $debit = $compte->ecritures->sum('debit');
            $totalProduits += ($credit - $debit);
        }

        foreach ($charges as $compte) {
            $debit = $compte->ecritures->sum('debit');
            $credit = $compte->ecritures->sum('credit');
            $totalCharges += ($debit - $credit);
        }

        return $totalProduits - $totalCharges;
    }

    /**
     * Compte de résultat
     */
    public function compteResultat(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $dateDebut = $request->get('date_debut', now()->startOfYear()->toDateString());
        $dateFin = $request->get('date_fin', now()->toDateString());

        // Produits (classe 7)
        $produits = Compte::where('entreprise_id', $entrepriseId)
            ->whereHas('classeComptable', function($q) {
                $q->where('numero', '7');
            })
            ->with(['ecritures' => function($q) use ($dateDebut, $dateFin) {
                $q->whereHas('journal', function($j) use ($dateDebut, $dateFin) {
                    $j->whereBetween('date_ecriture', [$dateDebut, $dateFin])
                      ->where('statut', '!=', 'annule');
                });
            }])
            ->orderBy('numero')
            ->get();

        // Charges (classe 6)
        $charges = Compte::where('entreprise_id', $entrepriseId)
            ->whereHas('classeComptable', function($q) {
                $q->where('numero', '6');
            })
            ->with(['ecritures' => function($q) use ($dateDebut, $dateFin) {
                $q->whereHas('journal', function($j) use ($dateDebut, $dateFin) {
                    $j->whereBetween('date_ecriture', [$dateDebut, $dateFin])
                      ->where('statut', '!=', 'annule');
                });
            }])
            ->orderBy('numero')
            ->get();

        $totalProduits = 0;
        $totalCharges = 0;

        foreach ($produits as $compte) {
            $credit = $compte->ecritures->sum('credit');
            $debit = $compte->ecritures->sum('debit');
            $compte->montant = $credit - $debit; // Normalement créditeur
            $totalProduits += $compte->montant;
        }

        foreach ($charges as $compte) {
            $debit = $compte->ecritures->sum('debit');
            $credit = $compte->ecritures->sum('credit');
            $compte->montant = $debit - $credit; // Normalement débiteur
            $totalCharges += $compte->montant;
        }

        $resultat = $totalProduits - $totalCharges;

        return view('comptabilite.compte-resultat', compact('produits', 'charges', 'totalProduits', 'totalCharges', 'resultat', 'dateDebut', 'dateFin'));
    }

    /**
     * Configuration comptable des points de vente
     */
    public function configurationPdv(Request $request, $pointDeVenteId = null)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $pointsDeVente = \App\Models\PointDeVente::where('entreprise_id', $entrepriseId)->get();
        $comptes = Compte::where('entreprise_id', $entrepriseId)->orderBy('numero')->get();
        
        $pointDeVente = null;
        if ($pointDeVenteId) {
            $pointDeVente = \App\Models\PointDeVente::with(['compteCaisse', 'compteVente', 'compteClient'])
                ->findOrFail($pointDeVenteId);
        }

        return view('comptabilite.configuration-pdv', compact('pointsDeVente', 'comptes', 'pointDeVente'));
    }

    /**
     * Sauvegarde de la configuration comptable d'un point de vente
     */
    public function sauvegarderConfigurationPdv(Request $request, $pointDeVenteId)
    {
        $request->validate([
            'compte_caisse_id' => 'nullable|exists:comptes,id',
            'compte_vente_id' => 'nullable|exists:comptes,id',
            'compte_client_id' => 'nullable|exists:comptes,id',
            'comptabilite_active' => 'boolean'
        ]);

        $pointDeVente = \App\Models\PointDeVente::findOrFail($pointDeVenteId);
        
        $pointDeVente->update([
            'compte_caisse_id' => $request->compte_caisse_id,
            'compte_vente_id' => $request->compte_vente_id,
            'compte_client_id' => $request->compte_client_id,
            'comptabilite_active' => $request->has('comptabilite_active')
        ]);

        return redirect()->back()->with('success', 'Configuration comptable sauvegardée avec succès.');
    }

    /**
     * Export PDF du journal
     */
    public function exportJournalPdf(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $dateDebut = $request->get('date_debut', now()->startOfMonth()->toDateString());
        $dateFin = $request->get('date_fin', now()->toDateString());

        $journaux = JournalComptable::with(['pointDeVente', 'user', 'ecritures.compte'])
            ->parEntreprise($entrepriseId)
            ->parPeriode($dateDebut, $dateFin)
            ->where('statut', '!=', 'annule')
            ->orderBy('date_ecriture')
            ->orderBy('created_at')
            ->get();

        $entreprise = \App\Models\Entreprise::find($entrepriseId);

        $pdf = Pdf::loadView('comptabilite.journal-pdf', compact('journaux', 'entreprise', 'dateDebut', 'dateFin'));
        
        return $pdf->download("journal-comptable-{$dateDebut}-{$dateFin}.pdf");
    }

    /**
     * Export PDF de la balance
     */
    public function exportBalancePdf(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $date = $request->get('date', now()->toDateString());
        
        $comptes = Compte::where('entreprise_id', $entrepriseId)
            ->with(['ecritures' => function($q) use ($date) {
                $q->whereHas('journal', function($j) use ($date) {
                    $j->where('date_ecriture', '<=', $date)
                      ->where('statut', '!=', 'annule');
                });
            }])
            ->orderBy('numero')
            ->get();

        $balance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($comptes as $compte) {
            $debitPeriode = $compte->ecritures->sum('debit');
            $creditPeriode = $compte->ecritures->sum('credit');
            
            if ($compte->type === 'actif') {
                $solde = $compte->solde_initial + $debitPeriode - $creditPeriode;
                $soldeDebit = $solde > 0 ? $solde : 0;
                $soldeCredit = $solde < 0 ? abs($solde) : 0;
            } else {
                $solde = $compte->solde_initial + $creditPeriode - $debitPeriode;
                $soldeCredit = $solde > 0 ? $solde : 0;
                $soldeDebit = $solde < 0 ? abs($solde) : 0;
            }

            $balance[] = [
                'compte' => $compte,
                'debit_periode' => $debitPeriode,
                'credit_periode' => $creditPeriode,
                'solde_debit' => $soldeDebit,
                'solde_credit' => $soldeCredit
            ];

            $totalDebit += $soldeDebit;
            $totalCredit += $soldeCredit;
        }

        $entreprise = \App\Models\Entreprise::find($entrepriseId);

        $pdf = Pdf::loadView('comptabilite.balance-pdf', compact('balance', 'totalDebit', 'totalCredit', 'date', 'entreprise'));
        
        return $pdf->download("balance-comptable-{$date}.pdf");
    }

    /**
     * Export PDF du grand-livre détail d'un compte
     */
    public function exportGrandLivrePdf(Request $request, $compteId)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $dateDebut = $request->get('date_debut', now()->startOfMonth()->toDateString());
        $dateFin = $request->get('date_fin', now()->toDateString());
        
        $compte = Compte::with('classeComptable')->findOrFail($compteId);
        
        $ecritures = EcritureComptable::with(['journal', 'client', 'produit'])
            ->parCompte($compteId)
            ->when(true, function ($query) use ($dateDebut, $dateFin) {
                return $this->appliquerFiltreJournauxNonAnnes($query, $dateDebut, $dateFin);
            })
            ->orderBy('created_at')
            ->get();

        // Calcul du solde initial
        $soldeInitial = $compte->solde_initial;
        $classeNum = intval($compte->classeComptable->numero ?? 0);
        $isDebiteurClass = in_array($classeNum, [2,3,4,5,6]);

        $mouvementsAnterieurs = EcritureComptable::parCompte($compteId)
            ->whereHas('journal', function ($q) use ($dateDebut) {
                $q->where('date_ecriture', '<', $dateDebut)
                  ->where('statut', '!=', 'annule');
            })
            ->get();

        foreach ($mouvementsAnterieurs as $mvt) {
            if ($isDebiteurClass) {
                $soldeInitial += $mvt->debit - $mvt->credit;
            } else {
                $soldeInitial += $mvt->credit - $mvt->debit;
            }
        }

        $entreprise = \App\Models\Entreprise::find($entrepriseId);

        $pdf = Pdf::loadView('comptabilite.grand-livre-detail-pdf', compact('compte', 'ecritures', 'soldeInitial', 'dateDebut', 'dateFin', 'entreprise'));
        
        return $pdf->download("grand-livre-{$compte->numero}-{$dateDebut}-{$dateFin}.pdf");
    }

    /**
     * Export PDF du grand-livre général (tous les comptes)
     */
    public function exportGrandLivreGeneralPdf(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $dateDebut = $request->get('date_debut', now()->startOfMonth()->toDateString());
        $dateFin = $request->get('date_fin', now()->toDateString());
        
        $comptes = Compte::where('entreprise_id', $entrepriseId)
            ->with('classeComptable')
            ->orderBy('numero')
            ->get();
        $entreprise = \App\Models\Entreprise::find($entrepriseId);

        $pdf = Pdf::loadView('comptabilite.grand-livre-general-pdf', compact('comptes', 'dateDebut', 'dateFin', 'entreprise'));
        
        return $pdf->download("grand-livre-general-{$dateDebut}-{$dateFin}.pdf");
    }

    /**
     * Export PDF du bilan
     */
    public function exportBilanPdf(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $date = $request->get('date', now()->toDateString());

        $bilan = $this->construireBilan($entrepriseId, $date);
        $actifs = $bilan['actifs'];
        $passifs = $bilan['passifs'];
        $totalActif = $bilan['totalActif'];
        $totalPassif = $bilan['totalPassif'];

        // Calcul du résultat de l'exercice (à ajouter au passif)
        $resultatExercice = $this->calculerResultatExercice($entrepriseId, $date);
        
        // Si bénéfice, on l'ajoute au passif
        if ($resultatExercice > 0) {
            $totalPassif += $resultatExercice;
        } else if ($resultatExercice < 0) {
            // Si perte, on l'ajoute à l'actif (en valeur absolue)
            $totalActif += abs($resultatExercice);
        }

        $entreprise = \App\Models\Entreprise::find($entrepriseId);

        $pdf = Pdf::loadView('comptabilite.bilan-pdf', compact('actifs', 'passifs', 'totalActif', 'totalPassif', 'date', 'resultatExercice', 'entreprise'));
        
        return $pdf->download("bilan-comptable-{$date}.pdf");
    }

    /**
     * Export PDF du compte de résultat
     */
    public function exportCompteResultatPdf(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $dateDebut = $request->get('date_debut', now()->startOfYear()->toDateString());
        $dateFin = $request->get('date_fin', now()->toDateString());

        // Produits (classe 7)
        $produits = Compte::where('entreprise_id', $entrepriseId)
            ->whereHas('classeComptable', function($q) {
                $q->where('numero', '7');
            })
            ->with(['ecritures' => function($q) use ($dateDebut, $dateFin) {
                $q->whereHas('journal', function($j) use ($dateDebut, $dateFin) {
                    $j->whereBetween('date_ecriture', [$dateDebut, $dateFin])
                      ->where('statut', '!=', 'annule');
                });
            }])
            ->orderBy('numero')
            ->get();

        // Charges (classe 6)
        $charges = Compte::where('entreprise_id', $entrepriseId)
            ->whereHas('classeComptable', function($q) {
                $q->where('numero', '6');
            })
            ->with(['ecritures' => function($q) use ($dateDebut, $dateFin) {
                $q->whereHas('journal', function($j) use ($dateDebut, $dateFin) {
                    $j->whereBetween('date_ecriture', [$dateDebut, $dateFin])
                      ->where('statut', '!=', 'annule');
                });
            }])
            ->orderBy('numero')
            ->get();

        $totalProduits = 0;
        $totalCharges = 0;

        foreach ($produits as $compte) {
            $credit = $compte->ecritures->sum('credit');
            $debit = $compte->ecritures->sum('debit');
            $compte->montant = $credit - $debit; // Normalement créditeur
            $totalProduits += $compte->montant;
        }

        foreach ($charges as $compte) {
            $debit = $compte->ecritures->sum('debit');
            $credit = $compte->ecritures->sum('credit');
            $compte->montant = $debit - $credit; // Normalement débiteur
            $totalCharges += $compte->montant;
        }

        $resultat = $totalProduits - $totalCharges;
        $entreprise = \App\Models\Entreprise::find($entrepriseId);

        $pdf = Pdf::loadView('comptabilite.compte-resultat-pdf', compact('produits', 'charges', 'totalProduits', 'totalCharges', 'resultat', 'dateDebut', 'dateFin', 'entreprise'));
        
        return $pdf->download("compte-resultat-{$dateDebut}-{$dateFin}.pdf");
    }
}
