<?php
namespace App\Http\Controllers;

use App\Models\PointDeVente;
use App\Models\Compte;
use App\Models\EntreeSortie;
use App\Models\StockJournalier;
use App\Services\ComptabiliteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class MouvementPointDeVenteController extends Controller
{
    // Affiche les mouvements de la session en cours pour un point de vente
    public function index(Request $request, $pointDeVenteId)
    {
        $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);
        $comptes = Compte::where('entreprise_id', $pointDeVente->entreprise_id)->orderBy('nom')->get();
        $q = $request->query('q');
        $selectionSession = $this->selectionSession(
            $pointDeVente,
            $request->query('session_from'),
            $request->query('session_to')
        );
        $sessions = $selectionSession['sessions'];
        $selectedSessionFrom = $selectionSession['selectedSessionFrom'];
        $selectedSessionTo = $selectionSession['selectedSessionTo'];
        $sessionDebut = $selectionSession['sessionDebut'];
        $sessionFin = $selectionSession['sessionFin'];
        $mouvementsQuery = $this->requeteMouvementsSession($pointDeVente, $selectionSession);

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
        return view('mouvements.mvmpdv', compact(
            'pointDeVente', 'comptes', 'mouvements', 'totalEntree', 'totalSortie',
            'sessionDebut', 'sessionFin', 'sessions', 'selectedSessionFrom', 'selectedSessionTo'
        ));
    }

    public function store(Request $request, $pointDeVenteId)
    {
        $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);

        if (!$this->intervalleSession($pointDeVente)) {
            return redirect()->back()->withErrors(['error' => 'Impossible d’ajouter un mouvement sans session ouverte.']);
        }

        $data = $request->validate([
            'compte_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('comptes', 'id')->where('entreprise_id', $pointDeVente->entreprise_id),
            ],
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
        $q = $request->query('q');
        $selectionSession = $this->selectionSession(
            $pointDeVente,
            $request->query('session_from'),
            $request->query('session_to')
        );
        $sessionDebut = $selectionSession['sessionDebut'];
        $sessionFin = $selectionSession['sessionFin'];
        $mouvementsQuery = $this->requeteMouvementsSession($pointDeVente, $selectionSession);

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

        $dateFrom = $sessionDebut?->format('d/m/Y H:i');
        $dateTo = $sessionFin?->format('d/m/Y H:i');
        $pdf = Pdf::loadView('mouvements.pdf', compact('pointDeVente','mouvements','totalEntree','totalSortie','dateFrom','dateTo','q'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('mouvements_'.$pointDeVente->id.'_'.now()->format('Ymd_His').'.pdf');
    }

    private function requeteMouvementsSession(PointDeVente $pointDeVente, array $selectionSession)
    {
        $mouvementsQuery = EntreeSortie::with('compte')
            ->where('point_de_vente_id', $pointDeVente->id)
            ->whereHas('compte', fn ($query) => $query->where('entreprise_id', $pointDeVente->entreprise_id));

        if (!$selectionSession['sessionDebut'] || !$selectionSession['sessionFin']) {
            return $mouvementsQuery->whereRaw('1 = 0');
        }

        $mouvementsQuery->whereBetween('created_at', [
            $selectionSession['sessionDebut'],
            $selectionSession['sessionFin'],
        ]);

        return $mouvementsQuery;
    }

    private function selectionSession(PointDeVente $pointDeVente, ?string $sessionFrom, ?string $sessionTo): array
    {
        $stocks = StockJournalier::where('point_de_vente_id', $pointDeVente->id)
            ->whereNotNull('session')
            ->orderByDesc('date')
            ->orderByDesc('session')
            ->orderByDesc('id')
            ->get(['session', 'date', 'validated_at', 'created_at']);

        $sessions = $stocks->groupBy('session')->map(function ($sessionStocks, $session) {
            $firstStock = $sessionStocks->first();
            $openedAt = $sessionStocks->pluck('validated_at')->filter()->first() ?? $firstStock->created_at;

            return (object) [
                'session' => (string) $session,
                'validated_at' => $openedAt,
            ];
        })->values();

        $sessionActive = $pointDeVente->etat === 'ouvert' ? $stocks->first()?->session : null;
        $sessionParDefaut = $sessionActive ?? $sessions->first()?->session;

        if (!$sessionFrom && !$sessionTo && $sessionParDefaut) {
            $sessionFrom = $sessionParDefaut;
            $sessionTo = $sessionParDefaut;
        } elseif ($sessionFrom && !$sessionTo) {
            $sessionTo = $sessionParDefaut ?? $sessionFrom;
        } elseif (!$sessionFrom && $sessionTo) {
            $sessionFrom = $sessions->last()?->session ?? $sessionTo;
        }

        $fromInfo = $sessions->firstWhere('session', (string) $sessionFrom);
        $toInfo = $sessions->firstWhere('session', (string) $sessionTo);
        if (!$fromInfo || !$toInfo) {
            return [
                'sessions' => $sessions,
                'selectedSessionFrom' => $sessionFrom,
                'selectedSessionTo' => $sessionTo,
                'sessionDebut' => null,
                'sessionFin' => null,
            ];
        }

        $fromOpening = \Carbon\Carbon::parse($fromInfo->validated_at);
        $toOpening = \Carbon\Carbon::parse($toInfo->validated_at);
        if ($fromOpening->greaterThan($toOpening)) {
            [$fromInfo, $toInfo] = [$toInfo, $fromInfo];
            [$sessionFrom, $sessionTo] = [$sessionTo, $sessionFrom];
            [$fromOpening, $toOpening] = [$toOpening, $fromOpening];
        }

        $closedAt = \App\Models\Historiquepdv::where('point_de_vente_id', $pointDeVente->id)
            ->where('etat', 'ferme')
            ->where('opened_at', $toInfo->validated_at)
            ->value('closed_at');

        if ($closedAt) {
            $sessionFin = \Carbon\Carbon::parse($closedAt);
        } elseif ($sessionActive && (string) $sessionActive === (string) $toInfo->session) {
            $sessionFin = now();
        } else {
            $sessionFin = $toOpening->copy()->endOfDay();
        }

        return [
            'sessions' => $sessions,
            'selectedSessionFrom' => (string) $sessionFrom,
            'selectedSessionTo' => (string) $sessionTo,
            'sessionDebut' => $fromOpening,
            'sessionFin' => $sessionFin,
        ];
    }

    private function intervalleSession(PointDeVente $pointDeVente): ?array
    {
        if ($pointDeVente->etat !== 'ouvert') {
            return null;
        }

        $stockSession = StockJournalier::where('point_de_vente_id', $pointDeVente->id)
            ->orderByDesc('date')
            ->orderByDesc('session')
            ->orderByDesc('id')
            ->first(['session', 'date', 'validated_at', 'created_at']);

        if (!$stockSession) {
            return null;
        }

        $sessionDebutBrut = $stockSession->validated_at ?? $stockSession->created_at;
        if (!$sessionDebutBrut) {
            return null;
        }

        return [\Carbon\Carbon::parse($sessionDebutBrut), now()];
    }
}
