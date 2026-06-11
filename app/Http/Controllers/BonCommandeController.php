<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BonCommande;
use App\Models\Panier;
use App\Models\Produit;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BonCommandeController extends Controller
{
    /**
     * Affiche la liste des bons de commande avec filtrage par date
     */
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::now()->toDateString());
        $startDate = Carbon::parse($date)->startOfDay();
        $endDate = Carbon::parse($date)->endOfDay();

        $bons = BonCommande::whereBetween('created_at', [$startDate, $endDate])
            ->with(['panier', 'serveuse', 'client', 'utilisateur'])
            ->orderByDesc('numero_bon')
            ->paginate(20);

        return view('bon_commande.index', [
            'bons' => $bons,
            'date' => $date,
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
                'produits_json' => json_encode($nouveauxProduits),
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
