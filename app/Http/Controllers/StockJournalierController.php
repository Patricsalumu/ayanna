<?php
namespace App\Http\Controllers;

use App\Models\StockJournalier;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf; // tout en haut

class StockJournalierController extends Controller
{
    // Affiche la fiche de stock journalier pour une date donnée (par défaut aujourd'hui)
    public function index(Request $request, $pointDeVenteId = null)
    {
        $date = $request->get('date', now()->toDateString());
        $session = $request->get('session');
        if (!$pointDeVenteId) {
            $pointDeVenteId = $request->get('point_de_vente_id');
        }
        if (!$pointDeVenteId) {
            $pointDeVenteId = auth()->user()->point_de_vente_id ?? null;
        }
        if (!$pointDeVenteId) {
            // Si aucun point de vente n'est fourni, prendre le premier point de vente existant
            $pointDeVenteId = \App\Models\PointDeVente::first()?->id;
        }
        if (!$pointDeVenteId) {
            // Aucun point de vente trouvé, retourner une vue vide ou un message
            return view('stock_journalier.index', [
                'stocks' => collect(),
                'date' => $date,
                'produits' => collect(),
                'pointDeVenteId' => null,
                'message' => 'Aucun point de vente disponible.'
            ]);
        }
        // Pour debug : récupérer le nom du point de vente courant
        $pointDeVente = \App\Models\PointDeVente::find($pointDeVenteId);
        $nomPointDeVente = $pointDeVente ? $pointDeVente->nom : null;
        // Récupérer toutes les sessions distinctes du jour pour ce point de vente
        $sessions = StockJournalier::where('date', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->orderByDesc('session')
            ->pluck('session')
            ->unique()
            ->values();
        // Si aucune session sélectionnée, prendre la dernière
        if (!$session && $sessions->count() > 0) {
            $session = $sessions->first();
        }
        // Filtrer les stocks de la session sélectionnée
        $stocks = StockJournalier::with('produit')
            ->where('date', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->when($session, function($q) use ($session) {
                $q->where('session', $session);
            })
            ->get();
        // Filtrer les produits liés à ce point de vente
        $produits = $pointDeVente->produits()->orderBy('nom')->get();
        return view('stock_journalier.index', compact('stocks', 'date', 'produits', 'pointDeVenteId', 'nomPointDeVente', 'sessions', 'session'));
    }

    // Saisie ou modification de la quantité ajoutée du stock journalier pour un produit
    public function storeqtajoute(Request $request)
    {
        $data = $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'date' => 'required|date',
            'quantite_ajoutee' => 'required|integer',
            'point_de_vente_id' => 'required|exists:points_de_vente,id',
        ]);

        // On récupère la dernière ligne de la dernière session du jour pour ce produit/point de vente
        $stock = StockJournalier::where('produit_id', $data['produit_id'])
            ->where('date', $data['date'])
            ->where('point_de_vente_id', $data['point_de_vente_id'])
            ->orderBy('session', 'desc')
            ->first();

        $quantite_ajoutee = $data['quantite_ajoutee'];
        if ($stock) {
            // Nouvelle logique : on additionne à l'ancienne valeur
            $stock->quantite_ajoutee = ($stock->quantite_ajoutee ?? 0) + $quantite_ajoutee;
            $stock->quantite_reste = ($stock->quantite_reste ?? 0) + $quantite_ajoutee;
            $stock->save();
        }
        return redirect()->back()->with('success', 'Quantité ajoutée enregistrée.');
    }

    // Saisie ou modification de la quantité initiale du stock journalier pour un produit
    public function storeqtinitial(Request $request)
    {
        $data = $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'date' => 'required|date',
            'quantite_initiale' => 'required|integer',
            'point_de_vente_id' => 'required|exists:points_de_vente,id',
        ]);

        $stock = StockJournalier::where('produit_id', $data['produit_id'])
            ->where('date', $data['date'])
            ->where('point_de_vente_id', $data['point_de_vente_id'])
            ->first();

        $quantite_ajoutee = $stock->quantite_ajoutee ?? 0;
        $quantite_vendue = $stock->quantite_vendue ?? 0;
        $quantite_initiale = $data['quantite_initiale'];
        $q_total = $quantite_initiale + $quantite_ajoutee;
        $quantite_reste = $stock ? ($stock->quantite_reste ?? ($q_total - $quantite_vendue)) : ($q_total - $quantite_vendue);

        $saveData = [
            'quantite_initiale' => $quantite_initiale,
            'quantite_ajoutee' => $quantite_ajoutee,
            'quantite_vendue' => $quantite_vendue,
            'quantite_reste' => $quantite_reste,
        ];

        StockJournalier::updateOrCreate(
            [
                'produit_id' => $data['produit_id'],
                'date' => $data['date'],
                'point_de_vente_id' => $data['point_de_vente_id'],
            ],
            $saveData
        );
        return redirect()->back()->with('success', 'Quantité initiale enregistrée.');
    }

    public function exportPdf(Request $request, $pointDeVenteId)
    {
        $date = $request->get('date', now()->toDateString());
        $session = $request->get('session');
        if (!$pointDeVenteId) {
            $pointDeVenteId = $request->get('point_de_vente_id');
        }
        if (!$pointDeVenteId) {
            $pointDeVenteId = auth()->user()->point_de_vente_id ?? null;
        }
        if (!$pointDeVenteId) {
            // Si aucun point de vente n'est fourni, prendre le premier point de vente existant
            $pointDeVenteId = \App\Models\PointDeVente::first()?->id;
        }
        if (!$pointDeVenteId) {
            // Aucun point de vente trouvé, retourner une vue vide ou un message
            return view('stock_journalier.index', [
                'stocks' => collect(),
                'date' => $date,
                'produits' => collect(),
                'pointDeVenteId' => null,
                'message' => 'Aucun point de vente disponible.'
            ]);
        }
        // Correction : récupération de l'objet PointDeVente et du nom
        $pointDeVente = \App\Models\PointDeVente::find($pointDeVenteId);
        $nomPointDeVente = $pointDeVente ? $pointDeVente->nom : null;
        // Récupérer toutes les sessions distinctes du jour pour ce point de vente
        $sessions = StockJournalier::where('date', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->orderByDesc('session')
            ->pluck('session')
            ->unique()
            ->values();
        // Si aucune session sélectionnée, prendre la dernière
        if (!$session && $sessions->count() > 0) {
            $session = $sessions->first();
        }
        // Filtrer les stocks de la session sélectionnée
        $stocks = StockJournalier::with('produit')
            ->where('date', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->when($session, function($q) use ($session) {
                $q->where('session', $session);
            })
            ->get();
        // Filtrer les produits liés à ce point de vente
        $produits = $pointDeVente ? $pointDeVente->produits()->orderBy('nom')->get() : collect();

        // Récupérer les informations d'ouverture et de fermeture de la session
        $sessionInfo = null;
        $heureOuverture = null;
        $heureFermeture = null;
        $sessionEnCours = false;
        
        if ($session) {
            // Récupérer l'historique correspondant à cette session
            // L'heure d'ouverture est stockée dans validated_at du stock_journalier
            $firstStock = StockJournalier::where('point_de_vente_id', $pointDeVenteId)
                ->where('session', $session)
                ->orderBy('created_at')
                ->first();
            
            if ($firstStock && $firstStock->validated_at) {
                $heureOuverture = \Carbon\Carbon::parse($firstStock->validated_at);
            }
            
            // Vérifier s'il y a une fermeture correspondante dans l'historique
            $fermeture = \App\Models\Historiquepdv::where('point_de_vente_id', $pointDeVenteId)
                ->where('etat', 'ferme')
                ->whereDate('closed_at', $date)
                ->where('opened_at', $firstStock?->validated_at)
                ->first();
            
            if ($fermeture && $fermeture->closed_at) {
                $heureFermeture = \Carbon\Carbon::parse($fermeture->closed_at);
            } else {
                $sessionEnCours = true;
            }
        }

        // Créer un nom de fichier unique avec la session
        $fileName = 'stock_journalier_'.$date;
        if ($session) {
            // Format de session plus lisible dans le nom de fichier
            if (strlen($session) === 14 && ctype_digit($session)) {
                $sessionFormatted = \Carbon\Carbon::createFromFormat('YmdHis', $session)->format('Y-m-d_H-i-s');
                $fileName .= '_session_'.$sessionFormatted;
            } else {
                $fileName .= '_session_'.$session;
            }
        }
        $fileName .= '.pdf';

        return Pdf::loadView('stock_journalier.pdf', [
            'stocks' => $stocks,
            'date' => $date,
            'session' => $session,
            'sessions' => $sessions,
            'produits' => $produits,
            'pointDeVenteId' => $pointDeVenteId,
            'nomPointDeVente' => $nomPointDeVente,
            'pointDeVente' => $pointDeVente,
            'heureOuverture' => $heureOuverture,
            'heureFermeture' => $heureFermeture,
            'sessionEnCours' => $sessionEnCours,
        ])->download($fileName);
    }

    /**
     * Affiche la fiche d'ouverture du stock journalier pour un point de vente (logique session)
     */
    public function ficheOuvertureStock(Request $request, $pointDeVenteId)
    {
        $pointDeVente = \App\Models\PointDeVente::findOrFail($pointDeVenteId);
        $produits = $pointDeVente->produits()->orderBy('nom')->get();
        $stocksDerniereSession = collect();
        // Trouver la dernière session (toutes dates confondues) pour ce point de vente
        $lastStock = \App\Models\StockJournalier::where('point_de_vente_id', $pointDeVenteId)
            ->orderByDesc('date')
            ->orderByDesc('session')
            ->first();
        $lastDate = $lastStock ? $lastStock->date : null;
        $lastSession = $lastStock ? $lastStock->session : null;
        // Récupérer les stocks de la dernière session
        $stocks = collect();
        if ($lastDate && $lastSession) {
            $stocks = \App\Models\StockJournalier::where('point_de_vente_id', $pointDeVenteId)
                ->where('date', $lastDate)
                ->where('session', $lastSession)
                ->get();
        }
        // Associer la quantité restée à chaque produit
        foreach ($produits as $produit) {
            $stock = $stocks->where('produit_id', $produit->id)->first();
            $stocksDerniereSession[$produit->id] = $stock ? $stock->quantite_reste : 0;
        }
        $date = now()->toDateString();
        $verrouille = false; // On ne verrouille que si la session est validée (géré à la validation)
        return view('stock_journalier.ouverture', compact('produits', 'pointDeVente', 'date', 'stocksDerniereSession', 'verrouille', 'lastSession'));
    }

    /**
     * Validation de la fiche d'ouverture du stock journalier : enregistre toutes les quantités initiales,
     * marque la fiche comme validée, ouvre le point de vente, puis redirige vers le plan de salle.
     */
    public function validerOuvertureStock(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'point_de_vente_id' => 'required|exists:points_de_vente,id',
            'quantite_initiale' => 'required|array',
        ]);
        $date = $request->date;
        $pointDeVenteId = $request->point_de_vente_id;
        $quantites = $request->quantite_initiale;
        $now = now();
        $session = $now->format('YmdHis'); // session unique
        // Pour chaque produit, on crée une NOUVELLE ligne stock_journalier (même s'il y en a déjà une pour aujourd'hui)
        foreach ($quantites as $produitId => $qte) {
            $stock = new \App\Models\StockJournalier();
            $stock->produit_id = $produitId;
            $stock->date = $date;
            $stock->point_de_vente_id = $pointDeVenteId;
            $stock->quantite_initiale = $qte;
            $stock->quantite_reste = $qte; // Correction : initialisation à la quantité initiale
            $stock->validated_at = $now;
            $stock->session = $session;
            $stock->save();
        }
        // Ouvre le point de vente (état = ouvert) ET historise l'ouverture
        $pointDeVente = \App\Models\PointDeVente::findOrFail($pointDeVenteId);
        if ($pointDeVente->etat !== 'ouvert') {
            $pointDeVente->etat = 'ouvert';
            $pointDeVente->save();
            \App\Models\Historiquepdv::create([
                'point_de_vente_id' => $pointDeVente->id,
                'user_id' => Auth::id(),
                'etat' => 'ouvert',
            ]);
        }
        // Redirige vers le plan de salle
        $salle = $pointDeVente->salles()->first();
        if ($salle) {
            return redirect()->route('salle.plan.vente', [
                'entreprise' => $pointDeVente->entreprise_id,
                'salle' => $salle->id,
                'point_de_vente_id' => $pointDeVente->id
            ])->with('success', 'Fiche d\'ouverture validée. Le point de vente est ouvert.');
        }
        return redirect()->route('pointsDeVente.show', [$pointDeVente->entreprise_id, $pointDeVente->id])
            ->with('success', 'Fiche d\'ouverture validée.');
    }

    /**
     * Ferme la session courante du point de vente :
     * - Enregistre la date/heure de fermeture et l'utilisateur
     * - Calcule la quantité restée pour chaque produit
     * - Historise la fermeture
     * - Met à jour le point de vente (état = fermé)
     */
    public function fermerSession(Request $request, $pointDeVenteId)
    {
        $now = now();
        $userId = Auth::id();
        $date = $now->toDateString();
        $heure = $now->format('H:i:s');
        $pointDeVente = \App\Models\PointDeVente::findOrFail($pointDeVenteId);

        // Vérification backend : empêcher la fermeture si un panier en cours existe
        $hasPanierEnCours = \App\Models\Panier::where('point_de_vente_id', $pointDeVenteId)
            ->where('status', 'en_cours')
            ->exists();
        if ($hasPanierEnCours) {
            return redirect()->back()->with('error', 'Impossible de fermer : il reste des paniers en cours pour ce point de vente.');
        }

        // Récupérer la dernière session ouverte pour ce point de vente (toutes dates confondues)
        $lastStock = StockJournalier::where('point_de_vente_id', $pointDeVenteId)
            ->orderByDesc('date')
            ->orderByDesc('session')
            ->first();
        $lastDate = $lastStock ? $lastStock->date : null;
        $lastSession = $lastStock ? $lastStock->session : null;
        // DEBUG : log avant test session
        Log::info('[Fermeture Session] Recherche session', [
            'point_de_vente_id' => $pointDeVenteId,
            'lastDate' => $lastDate,
            'lastSession' => $lastSession
        ]);
        if (!$lastSession) {
            return redirect()->back()->with('error', 'Aucune session à fermer.');
        }
        // Log pour debug : afficher la dernière date et session trouvées
        Log::info('[DEBUG FERMETURE] lastDate/lastSession', [
            'point_de_vente_id' => $pointDeVenteId,
            'lastDate' => $lastDate,
            'lastSession' => $lastSession
        ]);
        // Pour chaque produit de la session, calculer la quantité restée et la sauvegarder
        $stocks = StockJournalier::where('point_de_vente_id', $pointDeVenteId)
            ->where('date', $lastDate)
            ->where('session', $lastSession)
            ->get();
        foreach ($stocks as $stock) {
            $q_total = ($stock->quantite_initiale ?? 0) + ($stock->quantite_ajoutee ?? 0);
            $q_vendue = $stock->quantite_vendue ?? 0;
            $quantite_reste = $q_total - $q_vendue;
            $stock->quantite_reste = $quantite_reste;
            // $stock->closed_at = $now; // si colonne à ajouter, sinon ignorer
            $stock->save();
        }
        // Calcul du solde total de la session (somme des ventes)
        $solde = 0;
        foreach ($stocks as $stock) {
            $produit = $stock->produit;
            $prix = $produit ? $produit->prix_vente : 0;
            $solde += ($stock->quantite_vendue ?? 0) * $prix;
        }
        // Historiser la fermeture avec solde et infos
        \App\Models\Historiquepdv::create([
            'point_de_vente_id' => $pointDeVente->id,
            'user_id' => $userId,
            'etat' => 'ferme',
            'solde' => $solde,
            'opened_at' => $lastStock->validated_at ?? null,
            'closed_at' => $now,
            'opened_by' => $lastStock->validated_by ?? null,
            'closed_by' => $userId,
            'created_at' => $now,
        ]);
        // Fermer le point de vente
        $pointDeVente->etat = 'ferme';
        $pointDeVente->save();
        // DEBUG : log des infos session fermée
        Log::info('[Fermeture Session] Dernière session trouvée', [
            'point_de_vente_id' => $pointDeVenteId,
            'lastDate' => $lastDate,
            'lastSession' => $lastSession,
            'nbStocks' => $stocks->count(),
            'produits' => $stocks->pluck('produit_id')->toArray(),
        ]);
        return redirect()->route('pointsDeVente.show', [$pointDeVente->entreprise_id, $pointDeVente->id])
            ->with('success', 'Session fermée. Quantités sauvegardées.');
    }
}
