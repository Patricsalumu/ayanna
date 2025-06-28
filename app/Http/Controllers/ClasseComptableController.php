<?php

namespace App\Http\Controllers;

use App\Models\ClasseComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClasseComptableController extends Controller
{
    /**
     * Afficher la liste des classes comptables
     */
    public function index()
    {
        $classesComptables = ClasseComptable::withCount('comptes')
            ->with('comptes')
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->orderBy('numero')
            ->get();

        $classesPrincipales = $classesComptables->where('est_principale', true);
        $sousClasses = $classesComptables->where('est_principale', false);

        return view('classes-comptables.index', compact('classesPrincipales', 'sousClasses'));
    }

    /**
     * Afficher les détails d'une classe comptable
     */
    public function show(ClasseComptable $classeComptable)
    {
        $comptes = $classeComptable->comptes()->get();
        
        // Calculer le solde total
        $soldeTotal = $comptes->sum('solde_actuel');

        return view('classes-comptables.show', compact('classeComptable', 'comptes', 'soldeTotal'));
    }

    /**
     * Bilan comptable automatique 
     */
    public function bilan()
    {
        $actif = ClasseComptable::whereIn('numero', [1, 2, 3])
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->with('comptes')
            ->get();
            
        $passif = ClasseComptable::whereIn('numero', [4, 5])
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->with('comptes')
            ->get();

        return view('classes-comptables.bilan', compact('actif', 'passif'));
    }

    /**
     * Compte de résultat automatique
     */
    public function compteResultat()
    {
        $charges = \App\Models\Compte::where('classe_comptable_id', function($query) {
            $query->select('id')->from('classes_comptables')
                ->where('numero', 6)
                ->where('entreprise_id', Auth::user()->entreprise_id);
        })->get();
        
        $produits = \App\Models\Compte::where('classe_comptable_id', function($query) {
            $query->select('id')->from('classes_comptables')
                ->where('numero', 7)
                ->where('entreprise_id', Auth::user()->entreprise_id);
        })->get();

        $totalCharges = $charges->sum('solde_actuel');
        $totalProduits = $produits->sum('solde_actuel');
        $resultat = $totalProduits - $totalCharges;

        return view('classes-comptables.compte-resultat', compact('charges', 'produits', 'totalCharges', 'totalProduits', 'resultat'));
    }
}
