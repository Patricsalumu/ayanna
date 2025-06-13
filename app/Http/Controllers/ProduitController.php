<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Entreprise;
use App\Models\Categorie;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    /**
     * Afficher tous les produits liés à une entreprise.
     */
    public function all(Entreprise $entreprise)
    {
        $sort = request('sort', 'nom'); // tri par défaut sur 'nom'
        $direction = request('direction', 'asc');

        $produits = Produit::whereHas('categorie', function ($q) use ($entreprise) {
            $q->where('entreprise_id', $entreprise->id);
        })
        ->with('categorie')
        ->orderBy($sort, $direction)
        ->get();

        return view('produits.all', compact('entreprise', 'produits', 'sort', 'direction'));
    }

    /**
     * Formulaire de création ou de duplication d’un produit.
     */
    public function create(\App\Models\Entreprise $entreprise)
    {
        // Vérifie que l'entreprise existe
        if (!$entreprise) {
            return redirect()->route('entreprises.show', $entreprise->id)->with('error', 'Entreprise non trouvée');
        }
        // Récupération des catégories de l'entreprise
        $categories = \App\Models\Categorie::where('entreprise_id', $entreprise->id)->get();
        return view('produits.create', compact('entreprise', 'categories'));
    }

    /**
     * Enregistrer un nouveau produit (ou en dupliquer un).
     */
    public function store(Request $request, \App\Models\Entreprise $entreprise)
    {
        $request->validate([
            'categorie_id' => 'required|exists:categories,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_achat' => 'required|numeric|min:0',
            'prix_vente' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048', // validation de l'image
        ]);

        $categorie = \App\Models\Categorie::where('id', $request->categorie_id)
            ->where('entreprise_id', $entreprise->id)
            ->firstOrFail();

        $data = [
            'categorie_id' => $categorie->id,
            'nom' => $request->nom,
            'description' => $request->description,
            'prix_achat' => $request->prix_achat,
            'prix_vente' => $request->prix_vente,
        ];

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('produits', 'public');
        }

        \App\Models\Produit::create($data);

        return redirect()->route('produits.entreprise', $entreprise->id)->with('success', 'Produit ajouté avec succès');
    }

    /**
     * Formulaire d’édition d’un produit existant.
     */
    public function edit(Entreprise $entreprise, Produit $produit)
    {
        // Vérifie que le produit appartient à l'entreprise
        if ($produit->categorie->entreprise_id !== $entreprise->id) {
            abort(403);
        }
        $categories = Categorie::where('entreprise_id', $entreprise->id)->get();
        return view('produits.edit', compact('entreprise', 'produit', 'categories'));
    }

    /**
     * Mettre à jour un produit existant.
     */
    public function update(Request $request, Entreprise $entreprise, Produit $produit)
    {
        $request->validate([
            'categorie_id' => 'required|exists:categories,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_achat' => 'required|numeric|min:0',
            'prix_vente' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        // Vérifie que la catégorie appartient à l'entreprise
        $categorie = Categorie::where('id', $request->categorie_id)
            ->where('entreprise_id', $entreprise->id)
            ->firstOrFail();

        $data = [
            'categorie_id' => $categorie->id,
            'nom' => $request->nom,
            'description' => $request->description,
            'prix_achat' => $request->prix_achat,
            'prix_vente' => $request->prix_vente,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('produits', 'public');
        }

        $produit->update($data);

        return redirect()->route('produits.entreprise', $entreprise->id)->with('success', 'Produit mis à jour');
    }

    /**
     * Supprimer un produit.
     */
    public function destroy(Entreprise $entreprise, Produit $produit)
    {
        // Vérifie que le produit appartient à l'entreprise
        if ($produit->categorie->entreprise_id !== $entreprise->id) {
            abort(403);
        }
        $produit->delete();

        return redirect()->route('produits.entreprise', $entreprise->id)->with('success', 'Produit supprimé');
    }

    /**
     * Recherche AJAX avec tri dynamique.
     */
    public function searchAjax(Request $request, Entreprise $entreprise)
    {
        $query = Produit::whereHas('categorie', function ($q) use ($entreprise) {
            $q->where('entreprise_id', $entreprise->id);
        })->with('categorie');

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        $sortable = ['nom', 'prix_achat', 'prix_vente'];
        $sort = in_array($request->sort, $sortable) ? $request->sort : 'nom';
        $direction = $request->direction === 'desc' ? 'desc' : 'asc';

        $produits = $query->orderBy($sort, $direction)->get();

        return response()->json([
            'list' => view('produits.partials.list', compact('produits'))->render(),
            'grid' => view('produits.partials.grid', compact('produits'))->render(),
        ]);
    }

    /**
     * Valider les données d’un produit.    
     */
    private function validateProduit(Request $request)
    {
        return $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_achat' => 'required|numeric|min:0',
            'prix_vente' => 'required|numeric|min:0',
        ]);
    }

    /**
     * Sécurise l’accès à une catégorie appartenant à l’entreprise.* Sécurise l’accès à une catégorie appartenant à l’entreprise.
     */
    private function getCategorieEntreprise($categorie_id, Entreprise $entreprise)
    {
        return Categorie::where('id', $categorie_id)
                        ->where('entreprise_id', $entreprise->id)
                        ->firstOrFail();    
    }

    /**
     * Dupliquer un produit.
     */
    public function duplicate(Entreprise $entreprise, Produit $produit) 
    {
        // Vérifie que le produit appartient à l'entreprise
        if ($produit->categorie->entreprise_id !== $entreprise->id) {
            abort(403);
        }

        $newProduit = $produit->replicate();
        $newProduit->nom = $produit->nom . ' (copie)';
        $newProduit->save();

        return redirect()->route('produits.edit', [$entreprise->id, $newProduit->id])
            ->with('success', 'Produit dupliqué, vous pouvez le modifier.');
    }
}