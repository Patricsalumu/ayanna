<?php
namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PointDeVenteController extends Controller
{
    public function show(Entreprise $entreprise)
    {
        $user = Auth::user();
        // Si l'utilisateur n'a pas d'entreprise ou n'est pas associé à celle de l'URL
        if (!$user->entreprise_id || $user->entreprise_id != $entreprise->id) {
            // Si l'utilisateur a une entreprise, on le redirige vers la sienne
            if ($user->entreprise_id) {
                return redirect()->route('pointsDeVente.show', $user->entreprise_id)
                    ->with('error', "Vous n'avez pas accès à cette entreprise. Redirection vers votre entreprise.");
            } else {
                // Sinon, on l'invite à créer une entreprise
                return redirect()->route('entreprises.create')
                    ->with('error', "Vous n'avez pas encore d'entreprise. Veuillez en créer une pour accéder au système.");
            }
        }
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
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user->entreprise_id || $user->entreprise_id != $entreprise->id) {
            if ($user->entreprise_id) {
                return redirect()->route('pointsDeVente.create', [$user->entreprise_id])
                    ->with('error', "Vous n'avez pas accès à cette entreprise. Redirection vers votre entreprise.");
            } else {
                return redirect()->route('entreprises.create')
                    ->with('error', "Vous n'avez pas encore d'entreprise. Veuillez en créer une pour accéder au système.");
            }
        }
        $module_id = request('module_id');
        $module = null;
        if ($module_id) {
            $module = \App\Models\Module::findOrFail($module_id);
        }
        $categories = \App\Models\Categorie::where('entreprise_id', $entreprise->id)->get();
        $salles = \App\Models\Salle::where('entreprise_id', $entreprise->id)->get();

        // Si AJAX, retourner uniquement le formulaire sans layout
        if (request()->ajax()) {
            return view('points_de_vente._form', compact('entreprise', 'module', 'categories', 'salles'))->render();
        }
        // Sinon, vue classique
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

        return redirect()->route('pointsDeVente.show', [$entreprise->id, $pointDeVente->id, 'module_id' => $validated['module_id']])
            ->with('success', 'Point de vente créé !');
    }

    /**
     * Dupliquer un point de vente (POST AJAX)
     */
    public function duplicate(Entreprise $entreprise, $pointDeVenteId)
    {
        $original = $entreprise->pointsDeVente()->findOrFail($pointDeVenteId);

        $copieData = $original->toArray();
        $copieData['nom'] = $original->nom . ' COPIE';
        $copieData['module_id'] = $original->module_id;
        unset($copieData['id']);

        $copie = $entreprise->pointsDeVente()->create($copieData);
        $copie->categories()->sync($original->categories()->pluck('categories.id')->toArray());
        $copie->salles()->sync($original->salles()->pluck('salles.id')->toArray());

        if (request()->ajax()) {
            // Retourne l'URL d'édition pour rediriger l'utilisateur
            $editUrl = route('pointsDeVente.edit', [$entreprise->id, $copie->id]);
            return response()->json(['success' => true, 'copie_id' => $copie->id, 'edit_url' => $editUrl]);
        }

        return redirect()->route('pointsDeVente.edit', [$entreprise->id, $copie->id]);
    }

    public function edit(Entreprise $entreprise, $pointDeVenteId)
    {
        $pointDeVente = $entreprise->pointsDeVente()->findOrFail($pointDeVenteId);
        $categories = \App\Models\Categorie::where('entreprise_id', $entreprise->id)->get();
        $categoriesAssociees = $pointDeVente->categories()->pluck('categories.id')->toArray();
        $salles = \App\Models\Salle::where('entreprise_id', $entreprise->id)->get();
        $sallesAssociees = $pointDeVente->salles()->pluck('salles.id')->toArray();

        if (request()->ajax()) {
            return view('points_de_vente._form_edit', compact('entreprise', 'pointDeVente', 'categories', 'categoriesAssociees', 'salles', 'sallesAssociees'))->render();
        }
        return view('points_de_vente.edit', compact('entreprise', 'pointDeVente', 'categories', 'categoriesAssociees', 'salles', 'sallesAssociees'));
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
        return redirect()->route('pointsDeVente.show', [$entreprise->id, $pointDeVente->id, 'module_id' => $module_id])
            ->with('success', 'Point de vente modifié !');
    }

    public function destroy(Entreprise $entreprise, $pointDeVenteId)
    {
        $pointDeVente = $entreprise->pointsDeVente()->findOrFail($pointDeVenteId);
        $pointDeVente->delete();
        return redirect()->route('pointsDeVente.show', [$entreprise->id, $pointDeVente->id, 'module_id' => $pointDeVente->module_id])
            ->with('success', 'Point de vente supprimé !');
    }
}
?>