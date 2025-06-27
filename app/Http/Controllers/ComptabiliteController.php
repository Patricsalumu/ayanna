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

    /**
     * Grand livre - Mouvements par compte
     */
    public function grandLivre(Request $request, $compteId = null)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $dateDebut = $request->get('date_debut', now()->startOfMonth()->toDateString());
        $dateFin = $request->get('date_fin', now()->toDateString());
        
        $comptes = Compte::where('entreprise_id', $entrepriseId)->orderBy('numero')->get();
        
        if ($compteId) {
            $compte = Compte::with('classeComptable')->findOrFail($compteId);
            
            $ecritures = EcritureComptable::with(['journal', 'client', 'produit'])
                ->parCompte($compteId)
                ->whereHas('journal', function($q) use ($dateDebut, $dateFin) {
                    $q->whereBetween('date_ecriture', [$dateDebut, $dateFin]);
                })
                ->orderBy('created_at')
                ->get();

            // Calcul du solde initial
            $soldeInitial = $compte->solde_initial;
            $mouvementsAnterieurs = EcritureComptable::parCompte($compteId)
                ->whereHas('journal', function($q) use ($dateDebut) {
                    $q->where('date_ecriture', '<', $dateDebut);
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

        return view('comptabilite.grand-livre', compact('comptes', 'dateDebut', 'dateFin'));
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
                    $j->where('date_ecriture', '<=', $date);
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
        
        // Actifs (classes 1, 2, 3, 4, 5)
        $actifs = Compte::where('entreprise_id', $entrepriseId)
            ->where('type', 'actif')
            ->with(['ecritures' => function($q) use ($date) {
                $q->whereHas('journal', function($j) use ($date) {
                    $j->where('date_ecriture', '<=', $date);
                });
            }])
            ->orderBy('numero')
            ->get();

        // Passifs (classes 1, 2, 4, 7)
        $passifs = Compte::where('entreprise_id', $entrepriseId)
            ->where('type', 'passif')
            ->with(['ecritures' => function($q) use ($date) {
                $q->whereHas('journal', function($j) use ($date) {
                    $j->where('date_ecriture', '<=', $date);
                });
            }])
            ->orderBy('numero')
            ->get();

        $totalActif = 0;
        $totalPassif = 0;

        foreach ($actifs as $compte) {
            $debit = $compte->ecritures->sum('debit');
            $credit = $compte->ecritures->sum('credit');
            $solde = $compte->solde_initial + $debit - $credit;
            $compte->solde_bilan = max(0, $solde);
            $totalActif += $compte->solde_bilan;
        }

        foreach ($passifs as $compte) {
            $debit = $compte->ecritures->sum('debit');
            $credit = $compte->ecritures->sum('credit');
            $solde = $compte->solde_initial + $credit - $debit;
            $compte->solde_bilan = max(0, $solde);
            $totalPassif += $compte->solde_bilan;
        }

        return view('comptabilite.bilan', compact('actifs', 'passifs', 'totalActif', 'totalPassif', 'date'));
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
                    $j->whereBetween('date_ecriture', [$dateDebut, $dateFin]);
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
                    $j->whereBetween('date_ecriture', [$dateDebut, $dateFin]);
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
                    $j->where('date_ecriture', '<=', $date);
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
}
