<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;

class EntreprisesController extends Controller
{
    public function create()
    {
        return view('entreprises.create');
    }

    /**
     * Supprimer une entreprise
     */
    public function destroy(Entreprise $entreprise)
    {
        // Optionnel : vérification de l'autorisation
        // $this->authorize('delete', $entreprise);
        $entreprise->delete();
        return redirect()->route('dashboard')->with('success', 'Entreprise supprimée avec succès.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|unique:entreprises,email',
            'telephone' => 'nullable',
            'logo' => 'nullable|image|max:2048', // max 2MB
        ]);

        // Gestion de l’image
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $path;
        }

        // Création de l'entreprise
        $entreprise = Entreprise::create($validated);

        // Lier l'entreprise à l'utilisateur connecté
        $user = auth()->user();
        $user->entreprise_id = $entreprise->id;
        $user->save();

        // Rediriger directement vers le dashboard après création
        return redirect()->route('dashboard')->with('success', 'Entreprise créée avec succès !');
    }

    public function edit()
    {
        $entreprise = auth()->user()->entreprise;
        return view('entreprises.edit', compact('entreprise'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'module' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'site_web' => 'nullable|string|max:255',
            'identifiant_fiscale' => 'nullable|string|max:255',
            'registre_commerce' => 'nullable|string|max:255',
            'numero_entreprise' => 'nullable|string|max:255',
            'numero_tva' => 'nullable|string|max:255',
            'email' => 'nullable|email',
        ]);

        $entreprise = auth()->user()->entreprise;

        foreach ($validated as $key => $value) {
            if ($key !== 'logo') {
                $entreprise->$key = $value;
            }
        }

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $entreprise->logo = $logoPath;
        }
        $entreprise->save();

        return redirect()->route('dashboard')->with('success', 'Entreprise mise à jour.');
    }

    /**
     * Afficher les détails d'une entreprise
     */
    public function show(Entreprise $entreprise)
    {
        // Si l'entreprise n'existe pas (id 0 ou null), afficher un message et proposer la création
        if (!$entreprise || !$entreprise->id) {
            return view('entreprises.show', [
                'entreprise' => null,
                'modules' => collect(),
                'noEntrepriseMessage' => "Vous n'avez pas encore d'entreprise. Veuillez en créer une pour accéder aux modules."
            ]);
        }
        $modules = \App\Models\Module::all();
        return view('entreprises.show', compact('entreprise', 'modules'));
    }

    /**
     * Se connecter à une entreprise (redirige vers la page de détails)
     */
    public function login(Entreprise $entreprise)
    {
        session(['entreprise_active' => $entreprise->id]);
        return redirect()->route('entreprises.show', $entreprise->id);
    }

    // Fin de la classe propre, sans doublons ni balise PHP de fermeture
}