<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Panier;
use App\Models\Produit;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class PanierController extends Controller
{
    // Récupérer le panier d'une table avec client, serveuse et utilisateur en session
    public function getPanier(Request $request)
    {
        $table_id = $request->input('table_id');
        $point_de_vente_id = $request->input('point_de_vente_id');
        $panier = Panier::where('table_id', $table_id)
            ->where('point_de_vente_id', $point_de_vente_id)
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
        $point_de_vente_id = $request->input('point_de_vente_id');
        $quantite = $request->input('quantite', 1);

        $panier = Panier::firstOrCreate(
            ['table_id' => $table_id, 'point_de_vente_id' => $point_de_vente_id],
            ['produits' => json_encode([])]
        );

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

    // Modifier la quantité d'un produit
    public function modifierProduit(Request $request, $produit_id)
    {
        $table_id = $request->input('table_id');
        $point_de_vente_id = $request->input('point_de_vente_id');
        $quantite = $request->input('quantite');

        $panier = Panier::where('table_id', $table_id)
            ->where('point_de_vente_id', $point_de_vente_id)
            ->first();

        if (!$panier) return response()->json(['error' => 'Panier non trouvé'], 404);

        $produits = json_decode($panier->produits, true) ?? [];
        foreach ($produits as &$item) {
            if ($item['produit_id'] == $produit_id) {
                $item['quantite'] = $quantite;
                break;
            }
        }
        $panier->produits = json_encode($produits);
        $panier->save();

        return response()->json(['success' => true, 'produits' => $produits]);
    }

    // Supprimer un produit du panier
    public function supprimerProduit(Request $request, $produit_id)
    {
        $table_id = $request->input('table_id');
        $point_de_vente_id = $request->input('point_de_vente_id');

        $panier = Panier::where('table_id', $table_id)
            ->where('point_de_vente_id', $point_de_vente_id)
            ->first();

        if (!$panier) return response()->json(['error' => 'Panier non trouvé'], 404);

        $produits = collect(json_decode($panier->produits, true) ?? [])
            ->reject(fn($item) => $item['produit_id'] == $produit_id)
            ->values()
            ->all();

        $panier->produits = json_encode($produits);
        $panier->save();

        return response()->json(['success' => true, 'produits' => $produits]);
    }
}