<?php
namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\EntreeSortie;
use Illuminate\Http\Request;

class CompteController extends Controller
{
    // Liste des comptes
    public function index(Request $request)
    {
        $entreprise_id = $request->get('entreprise_id') ?? (auth()->user()->entreprise_id ?? null);
        $comptes = Compte::where('entreprise_id', $entreprise_id)->orderBy('numero')->get();
        $entreprise = \App\Models\Entreprise::find($entreprise_id);
        return view('comptes.index', compact('comptes', 'entreprise'));
    }

    // Formulaire création
    public function create(Request $request)
    {
        $entreprise_id = $request->get('entreprise_id') ?? (auth()->user()->entreprise_id ?? null);
        return view('comptes.create', compact('entreprise_id'));
    }

    // Enregistrement d'un compte
    public function store(Request $request)
    {
        $data = $request->validate([
            'numero' => 'required|unique:comptes',
            'nom' => 'required|string|max:255',
            'type' => 'required|in:actif,passif',
            'description' => 'nullable|string',
            'entreprise_id' => 'required|exists:entreprises,id',
        ]);
        $data['user_id'] = auth()->id();
        Compte::create($data);
        return redirect()->route('comptes.index')->with('success', 'Compte créé avec succès.');
    }

    // Formulaire édition
    public function edit(Compte $compte)
    {
        return view('comptes.edit', compact('compte'));
    }

    // Mise à jour
    public function update(Request $request, Compte $compte)
    {
        $data = $request->validate([
            'numero' => 'required|unique:comptes,numero,' . $compte->id,
            'nom' => 'required|string|max:255',
            'type' => 'required|in:actif,passif',
            'description' => 'nullable|string',
            'entreprise_id' => 'required|exists:entreprises,id',
        ]);
        $compte->update($data);
        return redirect()->route('comptes.index')->with('success', 'Compte modifié.');
    }

    // Suppression
    public function destroy(Compte $compte)
    {
        $compte->delete();
        return redirect()->route('comptes.index')->with('success', 'Compte supprimé.');
    }

    // Liste des entrées/sorties d'un compte
    public function mouvements(Compte $compte)
    {
        $mouvements = $compte->entreesSorties()->orderByDesc('created_at')->get();
        return view('comptes.mouvements', compact('compte', 'mouvements'));
    }

    // Ajout d'une entrée/sortie
    public function ajouterMouvement(Request $request, Compte $compte)
    {
        $data = $request->validate([
            'montant' => 'required|numeric',
            'libele' => 'required|string',
        ]);
        $data['user_id'] = auth()->id();
        $compte->entreesSorties()->create($data);
        return redirect()->route('comptes.mouvements', $compte)->with('success', 'Mouvement ajouté.');
    }

    // Suppression d'un mouvement
    public function supprimerMouvement(EntreeSortie $mouvement)
    {
        $compte = $mouvement->compte;
        $mouvement->delete();
        return redirect()->route('comptes.mouvements', $compte)->with('success', 'Mouvement supprimé.');
    }
}
