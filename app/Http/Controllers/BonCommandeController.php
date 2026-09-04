<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BonCommande;
use App\Models\Panier;
use App\Models\Produit;
use App\Models\StockJournalier;
use App\Models\Historiquepdv;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BonCommandeController extends Controller
{
    /**
    * Affiche la liste des bons de commande par session de vente
     */
    public function index(Request $request)
    {
        $selectedSession = $request->filled('session')
            ? (string) $request->input('session')
            : null;
        $search = trim((string) $request->input('search', ''));
        $clientFilter = trim((string) $request->input('client', ''));
        $pointDeVenteId = $request->integer('point_de_vente_id') ?: session('point_de_vente_id');

        $sessionsQuery = StockJournalier::with('pointDeVente')->orderByDesc('session');
        if ($pointDeVenteId) {
            $sessionsQuery->where('point_de_vente_id', $pointDeVenteId);
        }
        $sessions = $sessionsQuery->get()->groupBy('session')->map(function ($stocks, $session) {
            $first = $stocks->sortBy('validated_at')->first();
            return (object) [
                'session' => (string) $session,
                'point_de_vente_id' => $first->point_de_vente_id,
                'point_de_vente_nom' => $first->pointDeVente?->nom ?? 'N/A',
                'validated_at' => $first->validated_at ?? $first->created_at,
            ];
        })->values();

        if ($selectedSession === null && $sessions->isNotEmpty()) {
            $selectedSession = (string) $sessions->first()->session;
        }

        $query = BonCommande::query();
        if ($pointDeVenteId) {
            $query->whereHas('panier', function ($q) use ($pointDeVenteId) {
                $q->where('point_de_vente_id', $pointDeVenteId);
            });
        }

        $sessionInfo = $sessions->first(function ($session) use ($selectedSession) {
            return (string) $session->session === (string) $selectedSession;
        });
        if ($sessionInfo) {
            $query->whereHas('panier', function ($q) use ($sessionInfo) {
                $q->where('point_de_vente_id', $sessionInfo->point_de_vente_id);
            })->where('created_at', '>=', $sessionInfo->validated_at);

            $closedAt = Historiquepdv::where('point_de_vente_id', $sessionInfo->point_de_vente_id)
                ->where('etat', 'ferme')
                ->where('opened_at', $sessionInfo->validated_at)
                ->value('closed_at');
            if ($closedAt) {
                $query->where('created_at', '<=', $closedAt);
            }
        } else {
            $query->whereRaw('1 = 0');
        }

        $user = Auth::user();
        if (in_array($user?->role, ['Serveuse', 'serveuse'], true)) {
            $query->where('serveuse_id', $user->id);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('numero_bon', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'like', "%{$search}%");
                  })
                  ->orWhereHas('serveuse', function ($serveuseQuery) use ($search) {
                      $serveuseQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($clientFilter !== '') {
            $query->whereHas('client', function ($q) use ($clientFilter) {
                $q->where('nom', 'like', "%{$clientFilter}%");
            });
        }

        $bons = $query->with(['panier', 'serveuse', 'client', 'utilisateur'])
            ->orderByDesc('numero_bon')
            ->paginate(20)
            ->appends($request->query());

        $bonsCount = $bons->total();
        $produitsCount = 0;
        $montantTotal = 0;
        foreach ($bons as $bon) {
            $produits = is_string($bon->produits_json) ? json_decode($bon->produits_json, true) : ($bon->produits_json ?? []);
            if (is_array($produits)) {
                $produitsCount += count($produits);
            }
            $montantTotal += (float) ($bon->montant ?? 0);
        }

        return view('bon_commande.index', [
            'bons' => $bons,
            'selectedSession' => $selectedSession,
            'sessions' => $sessions,
            'search' => $search,
            'clientFilter' => $clientFilter,
            'pointDeVenteId' => $pointDeVenteId,
            'bonsCount' => $bonsCount,
            'produitsCount' => $produitsCount,
            'montantTotal' => $montantTotal,
        ]);
    }

    /**
     * Génère et crée un bon de commande
     */
    public function store(Request $request)
    {
        try {
            $panier_id = $request->input('panier_id');
            $serveuse_id = $request->input('serveuse_id');

            \Log::info('[BonCommande] store() appelé', ['panier_id' => $panier_id, 'serveuse_id' => $serveuse_id]);

            // Validation : vérifier qu'une serveuse est sélectionnée
            if (!$serveuse_id) {
                return response()->json([
                    'error' => 'Veuillez sélectionner une serveuse',
                    'code' => 'no_serveuse'
                ], 400);
            }

            // Récupérer le panier
            $panier = Panier::with('produits')->findOrFail($panier_id);

            \Log::info('[BonCommande] Panier trouvé', ['panier_id' => $panier->id, 'produits_count' => $panier->produits->count()]);

            // Récupérer les produits du panier via la relation
            $panierProduits = [];
            foreach ($panier->produits as $produit) {
                $panierProduits[] = [
                    'produit_id' => $produit->id,
                    'nom' => $produit->nom,
                    'quantite' => $produit->pivot->quantite,
                ];
            }

            \Log::info('[BonCommande] Produits du panier', ['produits' => $panierProduits]);

            // Récupérer les quantités déjà envoyées
            $quantitesSent = BonCommande::getQuantitesSent($panier_id);

            \Log::info('[BonCommande] Quantités envoyées', ['sent' => $quantitesSent]);

            // Calculer les différences et générer les nouveaux produits
            $nouveauxProduits = [];
            
            foreach ($panierProduits as $item) {
                $produit_id = $item['produit_id'];
                $quantiteActuelle = $item['quantite'];
                $quantiteEnvoyee = $quantitesSent[$produit_id] ?? 0;
                $difference = $quantiteActuelle - $quantiteEnvoyee;

                \Log::info('[BonCommande] Calcul différence', [
                    'produit_id' => $produit_id,
                    'actuelle' => $quantiteActuelle,
                    'envoyee' => $quantiteEnvoyee,
                    'difference' => $difference
                ]);

                if ($difference > 0) {
                    $nouveauxProduits[] = [
                        'produit_id' => $produit_id,
                        'nom' => $item['nom'],
                        'quantite' => $difference,
                    ];
                }
            }

            \Log::info('[BonCommande] Nouveaux produits', ['nouveaux' => $nouveauxProduits]);

            // Si aucun nouveau produit, retourner un message d'information
            if (empty($nouveauxProduits)) {
                return response()->json([
                    'message' => 'Aucun nouveau produit à envoyer en cuisine.',
                    'code' => 'no_new_products'
                ], 200);
            }

            // Générer le numéro de bon pour le jour actuel
            $numero_bon = BonCommande::getNextNumero();

            \Log::info('[BonCommande] Numéro généré', ['numero_bon' => $numero_bon]);

            // Créer le bon de commande
            $bon = BonCommande::create([
                'numero_bon' => $numero_bon,
                'panier_id' => $panier_id,
                'serveuse_id' => $serveuse_id,
                'client_id' => $panier->client_id,
                'utilisateur_id' => Auth::id(),
                'produits_json' => $nouveauxProduits,
            ]);

            \Log::info('[BonCommande] Bon créé', ['bon_id' => $bon->id, 'numero' => $bon->numero_bon]);

            return response()->json([
                'success' => true,
                'bon_id' => $bon->id,
                'numero_bon' => $bon->numero_bon,
                'message' => 'Bon de commande créé avec succès.',
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur création bon commande', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Une erreur est survenue lors de la création du bon de commande.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Affiche le formulaire d'impression d'un bon
     */
    public function dernierPourPanier($panierId)
    {
        $bon = BonCommande::where('panier_id', $panierId)
            ->orderByDesc('created_at')
            ->first();

        if (!$bon) {
            return response()->json([
                'success' => false,
                'error' => 'Aucun bon trouvé pour ce panier.',
            ], 404);
        }

        $produits = $bon->produits_json ?? [];

        if (is_string($produits)) {
            $decoded = json_decode($produits, true);
            $produits = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($produits)) {
            $produits = [];
        }

        $bonsDuPanier = BonCommande::where('panier_id', $panierId)
            ->orderBy('created_at')
            ->get();

        $commandeNo = 1;
        foreach ($bonsDuPanier as $index => $bonsItem) {
            if ((int) $bonsItem->id === (int) $bon->id) {
                $commandeNo = $index + 1;
                break;
            }
        }

        return response()->json([
            'success' => true,
            'bon_id' => $bon->id,
            'panier_id' => (int) $panierId,
            'numero_bon' => $bon->numero_bon,
            'commande_no' => $commandeNo,
            'produits' => $produits,
        ]);
    }

    public function show($id)
    {
        $bon = BonCommande::with(['panier.pointDeVente.entreprise', 'serveuse', 'client'])
            ->findOrFail($id);

        return view('bon_commande.show', [
            'bon' => $bon,
        ]);
    }

    /**
     * Réimprime un bon existant
     */
    public function reprint($id)
    {
        $bon = BonCommande::with(['panier.pointDeVente.entreprise', 'serveuse', 'client'])
            ->findOrFail($id);

        return view('bon_commande.print', [
            'bon' => $bon,
        ]);
    }

    /**
     * Retourne le HTML pour impression directe
     */
    public function print($id)
    {
        $bon = BonCommande::with(['panier.pointDeVente.entreprise', 'serveuse', 'client'])
            ->findOrFail($id);

        return view('bon_commande.print', [
            'bon' => $bon,
        ]);
    }
}
