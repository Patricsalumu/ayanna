<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Panier;
use App\Models\Produit;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PanierController extends Controller
{
    // Récupérer le panier d'une table avec client, serveuse et utilisateur en session
    public function getPanier(Request $request)
    {
        $table_id = $request->input('table_id');
        $panier = Panier::where('table_id', $table_id)
            ->where('status', 'en_cours')
            ->first();

        if (!$panier) {
            return response()->json([
                'panier' => [],
                'client' => null,
                'serveuse' => null,
                'user' => Auth::user(),
            ]);
        }

        $client = $panier->client_id ? Client::find($panier->client_id) : null;
        $serveuse = $panier->serveuse_id ? User::find($panier->serveuse_id) : null;
        $user = Auth::user();

        return response()->json([
            'panier' => json_decode($panier->produits, true),
            'client' => $client,
            'serveuse' => $serveuse,
            'user' => $user,
        ]);
    }

    // Ajouter un produit au panier
    public function ajouterProduit(Request $request, $produit_id)
    {
        $table_id = $request->input('table_id');
        $quantite = $request->input('quantite', 1);

        $panier = Panier::where('table_id', $table_id)
            ->where('status', 'en_cours')
            ->first();
        if (!$panier) {
            $panier = Panier::create([
                'table_id' => $table_id,
                'status' => 'en_cours',
                'produits' => json_encode([]),
            ]);
        }

        $produits = json_decode($panier->produits, true) ?? [];
        $found = false;
        foreach ($produits as &$item) {
            if ($item['produit_id'] == $produit_id) {
                $item['quantite'] += $quantite;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $produits[] = ['produit_id' => $produit_id, 'quantite' => $quantite];
        }
        $panier->produits = json_encode($produits);
        $panier->save();

        return response()->json(['success' => true, 'produits' => $produits]);
    }

    // Modifier la quantité d'un produit (nouvelle version, pivot)
    public function modifierProduit(Request $request, $produit_id)
    {
        try {
            $table_id = $request->input('table_id');
            $quantite = $request->input('quantite');

            $panier = Panier::where('table_id', $table_id)
                ->where('status', 'en_cours')
                ->first();

            if (!$panier) return response()->json(['error' => 'Panier non trouvé'], 404);

            $existant = $panier->produits()->where('produit_id', $produit_id)->first();
            if ($existant) {
                $panier->produits()->updateExistingPivot($produit_id, ['quantite' => $quantite]);
            } else if ($quantite > 0) {
                $panier->produits()->attach($produit_id, ['quantite' => $quantite]);
            }

            $panier->load('produits');
            $panierArray = $panier->produits->map(function($prod){
                return [
                    'id' => $prod->id,
                    'nom' => $prod->nom,
                    'prix' => $prod->prix_vente,
                    'qte' => $prod->pivot->quantite,
                    'image' => $prod->image ? asset('storage/'.$prod->image) : null,
                    'cat_id' => $prod->categorie_id,
                ];
            })->values()->toArray();

            return response()->json(['success' => true, 'panier' => $panierArray]);
        } catch (\Throwable $e) {
            Log::error('Erreur modifierProduit panier: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'error' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    // Supprimer un produit du panier
    public function supprimerProduit(Request $request, $produit_id)
    {
        $table_id = $request->input('table_id');
        $panier = Panier::where('table_id', $table_id)
            ->where('status', 'en_cours')
            ->first();

        if (!$panier) return response()->json(['error' => 'Panier non trouvé'], 404);

        // Marquer le produit comme supprimé (quantité 0 ans la table pivot)
        $existant = $panier->produits()->where('produit_id', $produit_id)->first();
        if ($existant) {
            $panier->produits()->updateExistingPivot($produit_id, ['quantite' =>0]);
        }

        $panier->load('produits');
        $panierArray = $panier->produits
            ->filter(fn($prod) => $prod->pivot->quantite !== null && $prod->pivot->quantite >= 0)
            ->map(function($prod){
                return [
                    'id' => $prod->id,
                    'nom' => $prod->nom,
                    'prix' => $prod->prix_vente,
                    'qte' => $prod->pivot->quantite,
                    'image' => $prod->image ? asset('storage/'.$prod->image) : null,
                    'cat_id' => $prod->categorie_id,
                ];
            })->values()->toArray();

        return response()->json(['success' => true, 'panier' => $panierArray]);
    }

    // Met à jour le client du panier (pivot DB)
    public function setClient(Request $request)
    {
        try {
            Log::info('[setClient] Requête reçue', $request->all());
            $table_id = $request->input('table_id');
            $client_id = $request->input('client_id');
            $opened_by = Auth::id();
            Log::info('[setClient] table_id='.$table_id.' client_id='.$client_id);
            $panier = Panier::where('table_id', $table_id)
                ->where('status', 'en_cours')
                ->first();
            if (!$panier) {
                $panier = Panier::create([
                    'table_id' => $table_id,
                    'status' => 'en_cours',
                    'opened_by' => $opened_by,
                ]);
            }
            $panier->client_id = $client_id;
            $panier->save();
            Log::info('[setClient] Panier id='.$panier->id.' client_id enregistré='.$panier->client_id);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Erreur setClient panier: '.$e->getMessage(), ['exception' => $e, 'request' => $request->all()]);
            return response()->json(['success' => false, 'error' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    // Met à jour la serveuse du panier (pivot DB)
    public function setServeuse(Request $request)
    {
        try {
            Log::info('[setServeuse] Requête reçue', $request->all());
            $table_id = $request->input('table_id');
            $serveuse_id = $request->input('serveuse_id');
            $opened_by = Auth::id();
            Log::info('[setServeuse] table_id='.$table_id.' serveuse_id='.$serveuse_id);
            $panier = Panier::where('table_id', $table_id)
                ->where('status', 'en_cours')
                ->first();
            if (!$panier) {
                $panier = Panier::create([
                    'table_id' => $table_id,
                    'status' => 'en_cours',
                    'opened_by' => $opened_by,
                ]);
            }
            $panier->serveuse_id = $serveuse_id;
            $panier->save();
            Log::info('[setServeuse] Panier id='.$panier->id.' serveuse_id enregistré='.$panier->serveuse_id);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Erreur setServeuse panier: '.$e->getMessage(), ['exception' => $e, 'request' => $request->all()]);
            return response()->json(['success' => false, 'error' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    // Libérer la table en supprimant le panier
    public function libererTable(Request $request)
    {
        try {
            $table_id = $request->input('table_id');
            $panier = Panier::where('table_id', $table_id)
                ->where('status', 'en_cours')
                ->first();
            if ($panier) {
                $panier->produits()->detach();
                $panier->delete();
            }
            // Trouver la salle de la table pour la redirection
            $table = \App\Models\TableResto::find($table_id);
            $salle_id = $table ? $table->salle_id : null;
            $entreprise_id = $table && $table->salle ? $table->salle->entreprise_id : null;
            return response()->json([
                'success' => true,
                'redirect_url' => $salle_id && $entreprise_id ? route('salle.plan.vente', [
                    'entreprise' => $entreprise_id,
                    'salle' => $salle_id,
                    'point_de_vente_id' => $request->input('point_de_vente_id')
                ]) : null
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur libererTable: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'error' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    /**
     * Affiche tous les paniers du jour (quel que soit le statut)
     */
    public function paniersDuJour(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? ($user->entreprise->id ?? null);
        $today = now()->startOfDay();
        $paniers = Panier::whereDate('created_at', $today)
            ->whereHas('tableResto.salle', function($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            })
            ->with(['tableResto', 'serveuse', 'client', 'produits'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('paniers.jour', compact('paniers'));
    }

    /**
     * Annuler un panier (status = 'annulé')
     */
    public function annuler($id)
    {
        $panier = Panier::findOrFail($id);
        \Log::debug('[DEBUG PANIER ANNULER] Avant', ['id' => $id, 'status' => $panier->status]);
        if ($panier->status === 'en_cours') {
            $panier->status = 'annulé';
            $panier->save();
            \Log::debug('[DEBUG PANIER ANNULER] Après', ['id' => $id, 'status' => $panier->status]);
        }
        $requestFrom = request('from');
        if ($requestFrom === 'jour') {
            return redirect()->route('paniers.jour')->with('success', 'Panier annulé.');
        } elseif ($requestFrom === 'catalogue') {
            return redirect()->back()->with('success', 'Panier annulé.');
        }
        return redirect()->back()->with('success', 'Panier annulé.');
    }

    /**
     * Enregistrer un snapshot d'impression de panier
     */
    public function enregistrerImpression(Request $request, $panierId)
    {
        $panier = \App\Models\Panier::findOrFail($panierId);
        $user = Auth::user();
        $data = $request->validate([
            'total' => 'required|numeric',
            'produits' => 'required|array',
        ]);
        $impression = new \App\Models\ImpressionPanier();
        $impression->panier_id = $panier->id;
        $impression->user_id = $user ? $user->id : null;
        $impression->total = $data['total'];
        $impression->produits = $data['produits'];
        $impression->printed_at = now();
        $impression->save();
        // Marquer le panier comme imprimé si besoin
        $panier->is_printed = true;
        $panier->save();
        return response()->json(['success' => true, 'impression_id' => $impression->id]);
    }

    /**
     * Affiche tous les paniers (tous statuts confondus) avec filtres
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? ($user->entreprise->id ?? null);
        
        // Récupérer les points de vente pour le filtre
        $pointsDeVente = \App\Models\PointDeVente::where('entreprise_id', $entrepriseId)
            ->orderBy('nom')
            ->get();
        
        // Appliquer les filtres
        $query = Panier::query()
            ->whereHas('tableResto.salle', function($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            })
            ->with(['tableResto', 'serveuse', 'client', 'produits', 'pointDeVente']);
        
        // Filtre par point de vente
        if ($request->has('point_de_vente') && !empty($request->point_de_vente)) {
            $query->where('point_de_vente_id', $request->point_de_vente);
        }
        
        // Filtre par date
        if ($request->has('date_debut') && !empty($request->date_debut)) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        
        if ($request->has('date_fin') && !empty($request->date_fin)) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }
        
        // Filtre par statut
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Recherche globale
        if ($request->has('search') && !empty($request->search)) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function($q) use ($search) {
                // Recherche sur les tables
                $q->whereHas('tableResto', function($subq) use ($search) {
                    $subq->whereRaw('LOWER(numero) LIKE ?', [$search]);
                })
                // Recherche sur les serveuses
                ->orWhereHas('serveuse', function($subq) use ($search) {
                    $subq->whereRaw('LOWER(name) LIKE ?', [$search]);
                })
                // Recherche sur les clients
                ->orWhereHas('client', function($subq) use ($search) {
                    $subq->whereRaw('LOWER(nom) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(telephone) LIKE ?', [$search]);
                })
                // Recherche sur les produits
                ->orWhereHas('produits', function($subq) use ($search) {
                    $subq->whereRaw('LOWER(nom) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$search]);
                });
            });
        }
        
        // Tri des résultats
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $allowedSorts = ['table_id', 'point_de_vente_id', 'created_at', 'status'];
        
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Récupération des paniers avec pagination
        $paniers = $query->paginate(25);
        
        // Calcul des statistiques
        $montantTotal = $paniers->sum(function($panier) {
            return $panier->produits->sum(function($produit) {
                return max(0, $produit->pivot->quantite) * $produit->prix_vente;
            });
        });
        
        $paniersPayes = $paniers->where('status', 'payé')->count();
        
        return view('paniers.show', compact('paniers', 'pointsDeVente', 'montantTotal', 'paniersPayes'));
    }

    /**
     * Récupérer les détails d'un panier pour la modale AJAX
     */
    public function details($id)
    {
        try {
            Log::info('Récupération des détails du panier ID: ' . $id);
            $panier = Panier::with([
                'tableResto', 
                'serveuse', 
                'client', 
                'produits.categorie',
                'paiements',
                'pointDeVente',
                'commande', // Ajout de la relation avec la commande
                'impressions.user' // Ajout des impressions avec l'utilisateur qui a effectué l'impression
            ])->findOrFail($id);
            
            Log::info('Panier trouvé', [
                'panier_id' => $panier->id,
                'status' => $panier->status,
                'has_commande' => $panier->commande ? true : false,
                'has_paiements' => $panier->paiements && $panier->paiements->count() > 0,
                'has_products' => $panier->produits && $panier->produits->count() > 0,
                'has_impressions' => $panier->impressions && $panier->impressions->count() > 0
            ]);
            
            // Vérifier s'il y a une commande associée
            if ($panier->commande) {
                Log::info('Commande trouvée pour le panier', [
                    'commande_id' => $panier->commande->id,
                    'mode_paiement' => $panier->commande->mode_paiement
                ]);
                // Assurez-vous que les informations de mode de paiement sont disponibles
                $panier->mode_paiement_commande = $panier->commande->mode_paiement;
            }
            
            return response()->json($panier);
        } catch (\Exception $e) {
            Log::error('Erreur details panier: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la récupération des détails du panier: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Générer un reçu pour impression
     */
    public function printReceipt($id)
    {
        try {
            $panier = Panier::with([
                'tableResto', 
                'serveuse', 
                'client', 
                'produits.categorie',
                'paiements',
                'pointDeVente',
                'commande' // Ajout de la relation avec la commande
            ])->findOrFail($id);
            
            // Récupération des infos de l'entreprise pour le reçu
            $entreprise = \App\Models\Entreprise::find($panier->pointDeVente->entreprise_id ?? Auth::user()->entreprise_id);
            
            // Vérifier s'il y a une commande associée
            if ($panier->commande) {
                // Assurez-vous que les informations de mode de paiement sont disponibles
                $panier->mode_paiement_commande = $panier->commande->mode_paiement;
            }
            
            return response()->json([
                'panier' => $panier,
                'entreprise' => $entreprise
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur printReceipt panier: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la génération du reçu: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Liste l'historique de toutes les impressions de paniers
     * Signale les impressions dont le panier n'existe plus
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function historiqueImpressions(Request $request)
    {
        try {
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id ?? ($user->entreprise->id ?? null);
            
            // Base de la requête pour récupérer toutes les impressions
            $query = \App\Models\ImpressionPanier::with(['user'])
                ->orderBy('printed_at', 'desc');
            
            // Appliquer les filtres si nécessaires
            if ($request->has('date_debut') && !empty($request->date_debut)) {
                $query->whereDate('printed_at', '>=', $request->date_debut);
            }
            
            if ($request->has('date_fin') && !empty($request->date_fin)) {
                $query->whereDate('printed_at', '<=', $request->date_fin);
            }
            
            if ($request->has('user_id') && !empty($request->user_id)) {
                $query->where('user_id', $request->user_id);
            }
            
            // Récupération des impressions avec pagination
            $impressions = $query->paginate(50);
            
            // Calculer les statistiques
            $montantTotal = $impressions->sum('total');
            $paniersActifs = 0;
            $paniersSupprimes = 0;
            
            // Transformer les résultats pour inclure l'information sur les paniers supprimés
            foreach ($impressions as $impression) {
                // Vérifier si le panier existe toujours
                $panierExiste = $impression->panier_id && \App\Models\Panier::find($impression->panier_id);
                $impression->panier_supprime = !$panierExiste;
                
                // Incrémenter les compteurs
                if ($panierExiste) {
                    $paniersActifs++;
                } else {
                    $paniersSupprimes++;
                }
            }
            
            // Récupérer la liste des utilisateurs pour le filtre
            $users = \App\Models\User::where('entreprise_id', $entrepriseId)
                ->orderBy('name')
                ->get();
            
            return view('paniers.historique-impressions', [
                'impressions' => $impressions,
                'montantTotal' => $montantTotal,
                'paniersActifs' => $paniersActifs,
                'paniersSupprimes' => $paniersSupprimes,
                'users' => $users
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur historiqueImpressions: '.$e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Une erreur est survenue lors du chargement de l\'historique des impressions.');
            return redirect()->route('paniers.show')->with('error', 'Erreur lors de la récupération de l\'historique des impressions: ' . $e->getMessage());
        }
    }
    
    /**
     * Récupère les détails des produits d'une impression pour l'affichage dans la modale
     * 
     * @param int $id ID de l'impression
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImpressionProduits($id)
    {
        try {
            $impression = \App\Models\ImpressionPanier::with('user')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'id' => $impression->id,
                'created_at' => $impression->printed_at,
                'user' => $impression->user ? [
                    'id' => $impression->user->id,
                    'name' => $impression->user->name
                ] : null,
                'produits' => $impression->produits
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur getImpressionProduits: '.$e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la récupération des produits de l\'impression.'
            ], 500);
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des produits: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les données d'historique des impressions pour l'API AJAX
     */
    public function getHistoriqueImpressionsData(Request $request)
    {
        try {
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id ?? ($user->entreprise->id ?? null);
            
            // Base de la requête pour récupérer toutes les impressions
            $query = \App\Models\ImpressionPanier::with(['user'])
                ->orderBy('printed_at', 'desc');
            
            // Appliquer les filtres si nécessaires
            if ($request->has('date_debut') && !empty($request->date_debut)) {
                $query->whereDate('printed_at', '>=', $request->date_debut);
            }
            
            if ($request->has('date_fin') && !empty($request->date_fin)) {
                $query->whereDate('printed_at', '<=', $request->date_fin);
            }
            
            if ($request->has('user_id') && !empty($request->user_id)) {
                $query->where('user_id', $request->user_id);
            }
            
            // Récupération des impressions avec pagination
            $impressions = $query->paginate(10);
            
            // Calculer les statistiques
            $montantTotal = $impressions->sum('total');
            $paniersActifs = 0;
            $paniersSupprimes = 0;
            
            // Transformer les résultats pour inclure l'information sur les paniers supprimés
            $formattedImpressions = [];
            foreach ($impressions as $impression) {
                // Vérifier si le panier existe toujours
                $panierExiste = $impression->panier_id && \App\Models\Panier::find($impression->panier_id);
                
                // Incrémenter les compteurs
                if ($panierExiste) {
                    $paniersActifs++;
                } else {
                    $paniersSupprimes++;
                }
                
                // Formater l'impression pour l'API
                $formattedImpressions[] = [
                    'id' => $impression->id,
                    'panier_id' => $impression->panier_id,
                    'user' => $impression->user ? [
                        'id' => $impression->user->id,
                        'name' => $impression->user->name
                    ] : null,
                    'total' => $impression->total,
                    'printed_at' => $impression->printed_at,
                    'panier_supprime' => !$panierExiste
                ];
            }
            
            return response()->json([
                'success' => true,
                'impressions' => $formattedImpressions,
                'stats' => [
                    'montantTotal' => $montantTotal,
                    'paniersActifs' => $paniersActifs,
                    'paniersSupprimes' => $paniersSupprimes,
                ],
                'pagination' => [
                    'current_page' => $impressions->currentPage(),
                    'last_page' => $impressions->lastPage(),
                    'total' => $impressions->total(),
                    'per_page' => $impressions->perPage()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur getHistoriqueImpressionsData: '.$e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la récupération des données d\'historique des impressions.'
            ], 500);
        }
    }
}