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
use Barryvdh\DomPDF\Facade\Pdf;

class MouvementPointDeVenteController extends Controller
{
    // Affiche la page des mouvements du jour pour un point de vente
    public function index(Request $request, $pointDeVenteId)
    {
        $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);
        $comptes = Compte::where('entreprise_id', $pointDeVente->entreprise_id)->orderBy('nom')->get();

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $q = $request->query('q');

        $mouvementsQuery = EntreeSortie::whereHas('compte', function($qb) use ($pointDeVente) {
                $qb->where('entreprise_id', $pointDeVente->entreprise_id);
            });

        // Période
        if ($dateFrom && $dateTo) {
            $start = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $end = \Carbon\Carbon::parse($dateTo)->endOfDay();
            $mouvementsQuery = $mouvementsQuery->whereBetween('created_at', [$start, $end]);
        } elseif ($dateFrom) {
            $start = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $mouvementsQuery = $mouvementsQuery->where('created_at', '>=', $start);
        } elseif ($dateTo) {
            $end = \Carbon\Carbon::parse($dateTo)->endOfDay();
            $mouvementsQuery = $mouvementsQuery->where('created_at', '<=', $end);
        } else {
            $mouvementsQuery = $mouvementsQuery->whereDate('created_at', now()->toDateString());
        }

        // Recherche texte (compte nom, compte numero, libele)
        if ($q) {
            $mouvementsQuery = $mouvementsQuery->where(function($qb) use ($q) {
                $qb->where('libele', 'like', "%{$q}%")
                   ->orWhereHas('compte', function($q2) use ($q) {
                       $q2->where('nom', 'like', "%{$q}%")
                          ->orWhere('numero', 'like', "%{$q}%");
                   });
            });
        }

        $mouvements = $mouvementsQuery->orderByDesc('created_at')->get();
        $totalEntree = $mouvements->filter(fn($mvt) => !$mvt->annule && $mvt->type === 'entree')->sum('montant');
        $totalSortie = $mouvements->filter(fn($mvt) => !$mvt->annule && $mvt->type === 'sortie')->sum('montant');
        return view('mouvements.mvmpdv', compact('pointDeVente', 'comptes', 'mouvements', 'totalEntree', 'totalSortie'));
    }

    public function store(Request $request, $pointDeVenteId)
    {
        $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);
        
        $data = $request->validate([
            'compte_id' => 'required|exists:comptes,id',
            'montant' => 'required|numeric|min:0',
            'libele' => 'required|string|max:255',
            'type_mouvement' => 'required|in:entree,sortie', // Ajout du type explicite
        ]);

        try {
            DB::beginTransaction();
            
            // Récupérer le compte sélectionné
            $compte = Compte::findOrFail($data['compte_id']);
            
            // Utiliser le type spécifié dans le formulaire
            $type = $data['type_mouvement']; // 'entree' ou 'sortie'
            
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

    // Annuler (soft) un mouvement — marque 'annule' à true
    public function annuler(Request $request, $pointDeVenteId, $mouvementId)
    {
        try {
            $mvt = EntreeSortie::where('point_de_vente_id', $pointDeVenteId)->findOrFail($mouvementId);
            $mvt->annule = true;
            $mvt->save();
            return redirect()->route('mouvements.pdv', $pointDeVenteId)->with('success', 'Mouvement annulé (soft).');
        } catch (\Exception $e) {
            \Log::error('Erreur annulation mouvement: '.$e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Impossible d\'annuler le mouvement']);
        }
    }

    // Export PDF des mouvements filtrés
    public function exportPdf(Request $request, $pointDeVenteId)
    {
        $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $q = $request->query('q');

        $mouvementsQuery = EntreeSortie::whereHas('compte', function($qb) use ($pointDeVente) {
                $qb->where('entreprise_id', $pointDeVente->entreprise_id);
            });

        // Période
        if ($dateFrom && $dateTo) {
            $start = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $end = \Carbon\Carbon::parse($dateTo)->endOfDay();
            $mouvementsQuery = $mouvementsQuery->whereBetween('created_at', [$start, $end]);
        } elseif ($dateFrom) {
            $start = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $mouvementsQuery = $mouvementsQuery->where('created_at', '>=', $start);
        } elseif ($dateTo) {
            $end = \Carbon\Carbon::parse($dateTo)->endOfDay();
            $mouvementsQuery = $mouvementsQuery->where('created_at', '<=', $end);
        } else {
            $mouvementsQuery = $mouvementsQuery->whereDate('created_at', now()->toDateString());
        }

        // Recherche texte
        if ($q) {
            $mouvementsQuery = $mouvementsQuery->where(function($qb) use ($q) {
                $qb->where('libele', 'like', "%{$q}%")
                   ->orWhereHas('compte', function($q2) use ($q) {
                       $q2->where('nom', 'like', "%{$q}%")
                          ->orWhere('numero', 'like', "%{$q}%");
                   });
            });
        }

        $mouvements = $mouvementsQuery->orderByDesc('created_at')->get();
        $totalEntree = $mouvements->filter(fn($mvt) => !$mvt->annule && $mvt->type === 'entree')->sum('montant');
        $totalSortie = $mouvements->filter(fn($mvt) => !$mvt->annule && $mvt->type === 'sortie')->sum('montant');

        $pdf = Pdf::loadView('mouvements.pdf', compact('pointDeVente','mouvements','totalEntree','totalSortie','dateFrom','dateTo','q'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('mouvements_'.$pointDeVente->id.'_'.now()->format('Ymd_His').'.pdf');
    }
}
