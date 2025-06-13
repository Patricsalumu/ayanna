<?php
namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function show(Entreprise $entreprise)

    //verifie que l'utilisateur est authentifié et a accès à l'entreprise
    {
        // Vérifie si l'utilisateur a accès à l'entreprise
        if (!auth()->user()->entreprise || auth()->user()->entreprise->id !== $entreprise->id) {
            abort(403, 'Accès interdit à cette entreprise.');
        }

        // Vérifie si l'entreprise a des catégories
        if ($entreprise->categories->isEmpty()) {
            return redirect()->route('categories.create', $entreprise)
                ->with('info', 'Aucune catégorie trouvée. Veuillez en créer une.');
        }


        // On récupère uniquement les catégories de l'entreprise
        $categories = $entreprise->categories()->latest()->get();
        $module_id = request('module_id'); // récupère le module_id de la requête si présent
        return view('categories.show', compact('entreprise', 'categories', 'module_id'));

    }

    public function create(Entreprise $entreprise) {
        return view('categories.create', compact('entreprise'));
    }

    public function store(Request $request, Entreprise $entreprise)
    {
        // Vérifie l'accès
        if (!auth()->user()->entreprise || auth()->user()->entreprise->id !== $entreprise->id) {
            abort(403, 'Accès interdit à cette entreprise.');
        }

        // Validation
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        // Création de la catégorie liée à l'entreprise
        $entreprise->categories()->create([
            'nom' => $validated['nom'],
        ]);

        return redirect()->route('categories.show', $entreprise->id)
            ->with('success', 'Catégorie créée avec succès.');
    }

    // Edition (affichage du formulaire)
    public function edit(Entreprise $entreprise, Categorie $categorie)
    {
        // Vérifie l'accès
        if (!auth()->user()->entreprise || auth()->user()->entreprise->id !== $entreprise->id) {
            abort(403, 'Accès interdit à cette entreprise.');
        }
        // Vérifie que la catégorie appartient bien à l'entreprise
        if ($categorie->entreprise_id !== $entreprise->id) {
            abort(404);
        }
        return view('categories.edit', compact('entreprise', 'categorie'));
    }

    // Mise à jour
    public function update(Request $request, Entreprise $entreprise, Categorie $categorie)
    {
        if (!auth()->user()->entreprise || auth()->user()->entreprise->id !== $entreprise->id) {
            abort(403, 'Accès interdit à cette entreprise.');
        }
        if ($categorie->entreprise_id !== $entreprise->id) {
            abort(404);
        }
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
        ]);
        $categorie->update(['nom' => $validated['nom']]);
        return redirect()->route('categories.show', $entreprise->id)
            ->with('success', 'Catégorie modifiée avec succès.');
    }

    // Suppression
    public function destroy(Entreprise $entreprise, Categorie $categorie)
    {
        if (!auth()->user()->entreprise || auth()->user()->entreprise->id !== $entreprise->id) {
            abort(403, 'Accès interdit à cette entreprise.');
        }
        if ($categorie->entreprise_id !== $entreprise->id) {
            abort(404);
        }
        $categorie->delete();
        return redirect()->route('categories.show', $entreprise->id)
            ->with('success', 'Catégorie supprimée avec succès.');
    }
}