<?php
namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;

class PointDeVenteController extends Controller
{
    public function show(Entreprise $entreprise)
    {
        $module_id = request('module_id');
        $module = null;
        if ($module_id) {
            $module = \App\Models\Module::findOrFail($module_id);
            if (!$entreprise->modules->contains('id', $module->id)) {
                return redirect()->route('entreprises.show', $entreprise->id)
                    ->with('error', 'Ce module n\'est pas activé pour cette entreprise.');
            }
        }

        if ($module) {
            $pointsDeVente = $entreprise->pointsDeVente()->where('module_id', $module->id)->get();
        } else {
            $pointsDeVente = $entreprise->pointsDeVente;
        }

        return view('points_de_vente.show', compact('pointsDeVente','entreprise','module'));
    }

    public function create(Entreprise $entreprise)
    {
        $module_id = request('module_id');
        $module = null;
        if ($module_id) {
            $module = \App\Models\Module::findOrFail($module_id);
        }

        // Catégories de l'entreprise uniquement
        $categories = \App\Models\Categorie::where('entreprise_id', $entreprise->id)->get();

        // Salles liées à au moins un PDV de l'entreprise
        $pdvIds = $entreprise->pointsDeVente()->pluck('id');
        $salles = \App\Models\Salle::where('entreprise_id', $entreprise->id)->get();

        return view('points_de_vente.create', compact('entreprise', 'module', 'categories', 'salles'));
    }

    public function store(Request $request, Entreprise $entreprise)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'module_id' => 'required|exists:modules,id',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'salles' => 'array',
            'salles.*' => 'exists:salles,id',
        ]);
        $pointDeVente = $entreprise->pointsDeVente()->create([
            'nom' => $validated['nom'],
            'module_id' => $validated['module_id'],
        ]);
        $pointDeVente->categories()->sync($validated['categories'] ?? []);
        $pointDeVente->salles()->sync($validated['salles'] ?? []);

        return redirect()->route('pointsDeVente.show', [$entreprise->id, 'module_id' => $validated['module_id']])
            ->with('success', 'Point de vente créé !');
    }

    /**
     * Dupliquer un point de vente
     */
    public function duplicate(Entreprise $entreprise, $pointDeVenteId)
    {
        $original = $entreprise->pointsDeVente()->findOrFail($pointDeVenteId);

        $copieData = $original->toArray();
        $copieData['nom'] = $original->nom . ' COPIE';
        $copieData['categories'] = $original->categories()->pluck('categories.id')->toArray();
        $copieData['salles'] = $original->salles()->pluck('salles.id')->toArray();

        return redirect()->route('pointsDeVente.create', [
            $entreprise->id,
            'module_id' => $original->module_id
        ])->withInput($copieData);
    }

    public function edit(Entreprise $entreprise, $pointDeVenteId)
    {
        $pointDeVente = $entreprise->pointsDeVente()->findOrFail($pointDeVenteId);

        // Catégories de l'entreprise uniquement
        $categories = \App\Models\Categorie::where('entreprise_id', $entreprise->id)->get();
        $categoriesAssociees = $pointDeVente->categories()->pluck('categories.id')->toArray();

        // Salles liées à au moins un PDV de l'entreprise
        $pdvIds = $entreprise->pointsDeVente()->pluck('id');
        $salles = \App\Models\Salle::where('entreprise_id', $entreprise->id)->get();
        $sallesAssociees = $pointDeVente->salles()->pluck('salles.id')->toArray();

        return view('points_de_vente.edit', compact(
            'entreprise', 'pointDeVente', 'categories', 'categoriesAssociees', 'salles', 'sallesAssociees'
        ));
    }

    public function update(Request $request, Entreprise $entreprise, $pointDeVenteId)
    {
        $pointDeVente = $entreprise->pointsDeVente()->findOrFail($pointDeVenteId);
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'salles' => 'array',
            'salles.*' => 'exists:salles,id',
        ]);
        $pointDeVente->update(['nom' => $validated['nom']]);
        $pointDeVente->categories()->sync($validated['categories'] ?? []);
        $pointDeVente->salles()->sync($validated['salles'] ?? []);

        $module_id = $pointDeVente->module_id;
        return redirect()->route('pointsDeVente.show', [$entreprise->id, 'module_id' => $module_id])
            ->with('success', 'Point de vente modifié !');
    }

    public function destroy(Entreprise $entreprise, $pointDeVenteId)
    {
        $pointDeVente = $entreprise->pointsDeVente()->findOrFail($pointDeVenteId);
        $pointDeVente->delete();
        return redirect()->route('pointsDeVente.show', [$entreprise->id, 'module_id' => $pointDeVente->module_id])
            ->with('success', 'Point de vente supprimé !');
    }
}
?>