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
        Log::debug('[supprimerProduit] Reçu', [
            'produit_id' => $produit_id,
            'table_id' => $request->input('table_id'),
            'user_id' => Auth::id(),
            'body' => $request->all()
        ]);

        $table_id = $request->input('table_id');
        $panier = Panier::where('table_id', $table_id)
            ->where('status', 'en_cours')
            ->first();

        if (!$panier) return response()->json(['error' => 'Panier non trouvé'], 404);

        // Marquer le produit comme supprimé (quantité -1 dans la table pivot)
        $existant = $panier->produits()->where('produit_id', $produit_id)->first();
        if ($existant) {
            $panier->produits()->updateExistingPivot($produit_id, ['quantite' => -1]);
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
        $today = now()->startOfDay();
        $paniers = Panier::whereDate('created_at', $today)
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
}