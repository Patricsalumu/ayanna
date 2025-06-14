<?php
namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use App\Models\Salle;

/**
 * SalleController gère les opérations liées aux salles d'une entreprise.
 * Il permet de créer, modifier, supprimer et afficher les salles.
 */

class SalleController extends Controller
{

    public function edit(Entreprise $entreprise, $salleId)
    {
        // Correction : empêcher la modification en mode vente (si paramètre point_de_vente_id présent)
        if (request()->has('point_de_vente_id')) {
            return redirect()->back()->with('error', "Modification de salle impossible en mode vente.");
        }
        $salle = $entreprise->salles()->findOrFail($salleId);
        return view('salles.edit', compact('entreprise', 'salle'));
    }


    public function update(Request $request, Entreprise $entreprise, $salleId)
    {
        // Correction : empêcher la modification en mode vente (si paramètre point_de_vente_id présent)
        if ($request->has('point_de_vente_id')) {
            return redirect()->back()->with('error', "Modification de salle impossible en mode vente.");
        }
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
        ]);
        $salle = $entreprise->salles()->findOrFail($salleId);
        $salle->update(['nom' => $validated['nom']]);
        return redirect()->route('salles.show', $entreprise->id)
            ->with('success', 'Salle modifiée avec succès.');
    }

    public function destroy(Entreprise $entreprise, $salleId)
    {
        // Correction : empêcher la suppression en mode vente (si paramètre point_de_vente_id présent)
        if (request()->has('point_de_vente_id')) {
            return redirect()->back()->with('error', "Suppression de salle impossible en mode vente.");
        }
        $salle = $entreprise->salles()->findOrFail($salleId);
        $salle->delete();
        return redirect()->route('salles.show', $entreprise->id)
            ->with('success', 'Salle supprimée avec succès.');
    }
    public function store(Request $request, Entreprise $entreprise)
    {
        // Correction : empêcher l'ajout en mode vente (si paramètre point_de_vente_id présent)
        if ($request->has('point_de_vente_id')) {
            return redirect()->back()->with('error', "Ajout de salle impossible en mode vente.");
        }
        // Validation
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        // Création de la salle liée à l'entreprise
        $salle = $entreprise->salles()->create([
            'nom' => $validated['nom'],
        ]);

        // Si AJAX ou JSON attendu, on retourne la salle en JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['id' => $salle->id, 'nom' => $salle->nom]);
        }

        return redirect()->route('salles.show', $entreprise->id)
            ->with('success', 'Salle ajoutée avec succès.');
    }
    
    public function plan(Entreprise $entreprise, Salle $salle)
    {
        $salle->load('tables'); // Charge les tables liées à cette salle
        return view('salles.plan', compact('salle', 'entreprise'));
    }

    public function create(Entreprise $entreprise)
    {
        return view('salles.create', compact('entreprise'));
    }
    public function show(Entreprise $entreprise)
    {
        $entreprise->load('salles.tables'); // Eager loading
        return view('salles.show', compact('entreprise'));
    }
        /**
     * Affichage du plan de salle en mode vente (aucune modification possible)
     */
    public function planVente(Entreprise $entreprise, Salle $salle)
    {
        // On charge les tables de la salle
        $salle->load('tables');
        $pointDeVenteId = request('point_de_vente_id');
        $pointDeVente = \App\Models\PointDeVente::find($pointDeVenteId);
        $sallesLiees = $pointDeVente ? $pointDeVente->salles : collect([$salle]);

        // Nouvelle logique : on récupère les paniers en base pour chaque table de la salle
        $tableIds = $salle->tables->pluck('id');
        $paniers = \App\Models\Panier::whereIn('table_id', $tableIds)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->with('produits')
            ->get()
            ->keyBy('table_id');

        foreach ($salle->tables as $table) {
            $panier = $paniers[$table->id] ?? null;
            $qte = 0;
            $montant = 0;
            if ($panier) {
                foreach ($panier->produits as $prod) {
                    $qte += $prod->pivot->quantite;
                    $montant += $prod->pivot->quantite * $prod->prix_vente;
                }
            }
            $table->nb_commandes = $qte;
            $table->montant_total = $montant;
            $table->is_busy = $qte > 0;
        }

        return view('salles.plan-vente', [
            'entreprise' => $entreprise,
            'salle' => $salle,
            'salles' => $sallesLiees,
        ]);
    }

}
?>
