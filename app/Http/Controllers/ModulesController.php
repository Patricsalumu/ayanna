<?php
namespace App\Http\Controllers;
use App\Models\Module;
use App\Models\ModuleEntreprise;
use Illuminate\Http\Request;
use App\Models\PointDeVente;
use App\Models\Categorie;
use App\Models\Produit;
use App\Models\Salle;
use App\Models\Table;
use App\Models\Entreprise;

use App\Models\Defaults\PosRestaubarDefaults;

class ModulesController extends Controller
{
    // Méthode d'activation générique pour tous les modules (affiche juste un message de succès)
    public function activate(Module $module)
    {
        $entreprise = auth()->user()->entreprise;
        // Vérifie si l'association existe déjà
        $exists = ModuleEntreprise::where('entreprise_id', $entreprise->id)
            ->where('module_id', $module->id)
            ->exists();
        if (!$exists) {
            ModuleEntreprise::create([
                'entreprise_id' => $entreprise->id,
                'module_id' => $module->id,
            ]);
            $message = 'Le module "' . $module->nom . '" a été activé pour l\'entreprise.';
       
            // Si le module est le POS, on initialise les données par défaut    
            if ($module->nom === 'POS Restaubar') {
                $pointDeVente = PosRestaubarDefaults::initialiserPour($module, $entreprise);
                $message .= ' Les données par défaut ont été initialisées.';
                session(['module_id' => $module->id]);
                return redirect()->route('pointsDeVente.show', [$entreprise->id, $pointDeVente->id])
                    ->with('success', $message);
            } else {
                $message .= 'Les données non réinitialisées';
            }
        } else {
            $message = 'Ce module est déjà activé pour cette entreprise.';
            // Correction ici aussi si besoin
            $pointDeVente = PointDeVente::where('entreprise_id', $entreprise->id)->first();
            if (!$pointDeVente) {
                // Aucun point de vente créé, rediriger avec un message d'erreur
                return redirect()->route('entreprises.show', $entreprise->id)
                    ->with('error', 'Aucun point de vente n\'a été créé pour cette entreprise.');
            }
            return redirect()->route('pointsDeVente.show', [$entreprise->id, $pointDeVente->id])
                    ->with('success', $message);
        }
        return redirect()->route('entreprises.show', $entreprise->id)
            ->with('success', $message);
    }
    public function destroy(Module $module)
    {
        $module->delete();
        return redirect()->route('entreprises.show', auth()->user()->entreprise->id)
            ->with('success', 'Module supprimé avec succès !');
    }
    public function create()
    {
        return view('modules.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
            'disponible' => 'required|boolean',
        ]);

        // Gestion de l'upload de l'icône
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('modules/icons', 'public');
            $validated['icon'] = $iconPath;
        } else {
            unset($validated['icon']);
        }

        Module::create($validated);
        return redirect()->route('entreprises.show', auth()->user()->entreprise->id)
            ->with('success', 'Module créé avec succès !');
    }

    public function edit(Module $module)
    {
        return view('modules.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
            'disponible' => 'required|boolean',
        ]);

        // Gestion de l'upload de l'icône
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('modules/icons', 'public');
            $validated['icon'] = $iconPath;
        } else {
            unset($validated['icon']);
        }

        $module->update($validated);
        return redirect()->route('entreprises.show', auth()->user()->entreprise->id)
            ->with('success', 'Module mis à jour avec succès !');
    }

    // Méthode pour initialiser le module de gestion de restaurant
    protected function initialiserPOSParDefaut(Entreprise $entreprise, Module $module)
{
    // 1. Créer un point de vente lié à l’entreprise et au module
    $pointDeVente = PointDeVente::create([
        'nom' => PosRestaubarDefaults::pointDeVente()['nom'],
        'entreprise_id' => $entreprise->id,
        'module_id' => $module->id,
    ]);

    // 2. Catégories + Produits
    foreach (PosRestaubarDefaults::categories() as $nomCategorie) {
        $categorie = Categorie::create([
            'nom' => $nomCategorie,
        ]);

        // Lier à point de vente via table pivot
        $categorie->pointDeVentes()->attach($pointDeVente->id);

        // Produits de la catégorie
        $produits = PosRestaubarDefaults::produits()[$nomCategorie] ?? [];

        foreach ($produits as $produit) {
            Produit::create([
                'nom' => $produit['nom'],
                'description' => $produit['description'],
                'prix_achat' => $produit['prix_achat'],
                'prix_vente' => $produit['prix_vente'],
                'categorie_id' => $categorie->id,
            ]);
        }
    }

    // 3. Salle
    $salle = Salle::create([
        'nom' => PosRestaubarDefaults::salle(),
    ]);

    // Liaison salle <-> point de vente
    $salle->pointDeVentes()->attach($pointDeVente->id);

    // 4. Tables dans la salle
    foreach (PosRestaubarDefaults::tables() as $tableData) {
        Table::create([
            'numero' => $tableData['numero'],
            'forme' => $tableData['forme'],
            'salle_id' => $salle->id,
        ]);
    }
}
}