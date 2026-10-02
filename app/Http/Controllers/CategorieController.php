<?php
namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\Categorie;
use App\Models\Compte;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $comptes = $this->comptesParClasse($entreprise);
        $module_id = request('module_id'); // récupère le module_id de la requête si présent
        return view('categories.show', [
            'entreprise' => $entreprise,
            'categories' => $categories,
            'module_id' => $module_id,
            'comptesVente' => $comptes['7'],
            'comptesStock' => $comptes['3'],
            'comptesVariationStock' => $comptes['6'],
        ]);

    }

    public function create(Entreprise $entreprise) {
        $comptes = $this->comptesParClasse($entreprise);

        return view('categories.create', [
            'entreprise' => $entreprise,
            'comptesVente' => $comptes['7'],
            'comptesStock' => $comptes['3'],
            'comptesVariationStock' => $comptes['6'],
        ]);
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
            'compte_vente_id' => ['required', 'integer', Rule::in($this->comptesParClasse($entreprise)['7']->modelKeys())],
            'compte_stock_id' => ['required', 'integer', Rule::in($this->comptesParClasse($entreprise)['3']->modelKeys())],
            'compte_variation_stock_id' => ['required', 'integer', Rule::in($this->comptesParClasse($entreprise)['6']->modelKeys())],
        ]);

        // Création de la catégorie liée à l'entreprise
        $entreprise->categories()->create([
            'nom' => $validated['nom'],
            'compte_vente_id' => $validated['compte_vente_id'],
            'compte_stock_id' => $validated['compte_stock_id'],
            'compte_variation_stock_id' => $validated['compte_variation_stock_id'],
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
        $comptes = $this->comptesParClasse($entreprise);

        return view('categories.edit', [
            'entreprise' => $entreprise,
            'categorie' => $categorie,
            'comptesVente' => $comptes['7'],
            'comptesStock' => $comptes['3'],
            'comptesVariationStock' => $comptes['6'],
        ]);
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
            'compte_vente_id' => ['required', 'integer', Rule::in($this->comptesParClasse($entreprise)['7']->modelKeys())],
            'compte_stock_id' => ['required', 'integer', Rule::in($this->comptesParClasse($entreprise)['3']->modelKeys())],
            'compte_variation_stock_id' => ['required', 'integer', Rule::in($this->comptesParClasse($entreprise)['6']->modelKeys())],
        ]);
        $categorie->update([
            'nom' => $validated['nom'],
            'compte_vente_id' => $validated['compte_vente_id'],
            'compte_stock_id' => $validated['compte_stock_id'],
            'compte_variation_stock_id' => $validated['compte_variation_stock_id'],
        ]);
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

    private function comptesParClasse(Entreprise $entreprise): array
    {
        $comptes = Compte::with('classeComptable')
            ->where('entreprise_id', $entreprise->id)
            ->whereHas('classeComptable', function ($query) {
                $query->where(function ($classes) {
                    $classes->where('numero', 'like', '3%')
                        ->orWhere('numero', 'like', '6%')
                        ->orWhere('numero', 'like', '7%');
                });
            })
            ->orderBy('numero')
            ->get();

        return collect(['3', '6', '7'])
            ->mapWithKeys(fn ($classe) => [$classe => $comptes->filter(fn ($compte) => str_starts_with($compte->classeComptable->numero, $classe))->values()])
            ->all();
    }
}