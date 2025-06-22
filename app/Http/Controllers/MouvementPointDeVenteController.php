<?php
namespace App\Http\Controllers;

use App\Models\PointDeVente;
use App\Models\Compte;
use App\Models\EntreeSortie;
use Illuminate\Http\Request;

class MouvementPointDeVenteController extends Controller
{
    // Affiche la page des mouvements du jour pour un point de vente
    public function index($pointDeVenteId)
    {
        $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);
        $comptes = Compte::where('entreprise_id', $pointDeVente->entreprise_id)->orderBy('nom')->get();
        $mouvements = EntreeSortie::whereDate('created_at', now()->toDateString())
            ->whereHas('compte', function($q) use ($pointDeVente) {
                $q->where('entreprise_id', $pointDeVente->entreprise_id);
            })
            ->orderByDesc('created_at')
            ->get();
        $totalEntree = $mouvements->filter(fn($mvt) => $mvt->compte && $mvt->compte->type === 'actif')->sum('montant');
        $totalSortie = $mouvements->filter(fn($mvt) => $mvt->compte && $mvt->compte->type === 'passif')->sum('montant');
        return view('mouvements.mvmpdv', compact('pointDeVente', 'comptes', 'mouvements', 'totalEntree', 'totalSortie'));
    }

    // Ajoute un mouvement pour le point de vente
    public function store(Request $request, $pointDeVenteId)
    {
        $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);
        $data = $request->validate([
            'compte_id' => 'required|exists:comptes,id',
            'montant' => 'required|numeric',
            'libele' => 'required|string',
        ]);
        $data['user_id'] = auth()->id();
        $data['point_de_vente_id'] = $pointDeVente->id; // Ajout du point de vente
        EntreeSortie::create($data);
        return redirect()->route('mouvements.pdv', $pointDeVenteId)->with('success', 'Mouvement enregistré.');
    }
}
