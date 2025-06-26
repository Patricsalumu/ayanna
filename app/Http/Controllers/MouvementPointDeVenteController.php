<?php
namespace App\Http\Controllers;

use App\Models\PointDeVente;
use App\Models\Compte;
use App\Models\EntreeSortie;
use App\Services\ComptabiliteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
            'montant' => 'required|numeric|min:0',
            'libele' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // Récupérer le compte sélectionné
            $compte = Compte::findOrFail($data['compte_id']);
            
            // Déterminer le type basé sur le type de compte
            $type = $compte->type === 'actif' ? 'credit' : 'debit';
            
            // Données pour l'entrée/sortie
            $entreeData = [
                'compte_id' => $data['compte_id'],
                'montant' => $data['montant'],
                'libele' => $data['libele'],
                'type' => $type,
                'user_id' => Auth::id(),
                'point_de_vente_id' => $pointDeVente->id,
                'comptabilise' => false
            ];
            
            // Créer l'entrée/sortie
            $entree = EntreeSortie::create($entreeData);
            
            // Enregistrer en comptabilité via le service existant
            $comptabiliteService = new ComptabiliteService();
            $journal = $comptabiliteService->enregistrerMouvement($entree);
            
            DB::commit();
            Log::info('Mouvement enregistré et comptabilisé', [
                'entree_id' => $entree->id,
                'journal_id' => $journal->id,
                'compte' => $compte->nom,
                'montant' => $data['montant'],
                'type' => $type
            ]);
            
            return redirect()->route('mouvements.pdv', $pointDeVenteId)->with('success', 'Mouvement enregistré et comptabilisé avec succès.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'enregistrement du mouvement : ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()]);
        }
    }
}
