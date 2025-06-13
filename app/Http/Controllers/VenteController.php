<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PointDeVente;
use App\Models\Historiquepdv;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VenteController extends Controller
{
    /**
     * Affichage du catalogue produits pour la vente (PDV)
     */
    public function catalogue(Request $request, $pointDeVenteId)
    {
        $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);
        // Récupérer uniquement les catégories associées au point de vente (pas toutes celles de l'entreprise)
        $categories = $pointDeVente->categories;
        $categorieActive = $request->get('categorie');
        $search = $request->get('search');

        // Produits filtrés par catégorie et recherche
        $produitsQuery = \App\Models\Produit::query();
        if ($categorieActive) {
            $produitsQuery->where('categorie_id', $categorieActive);
        } else {
            // Produits de toutes les catégories du PDV
            $produitsQuery->whereIn('categorie_id', $categories->pluck('id'));
        }
        if ($search) {
            $produitsQuery->where('nom', 'like', '%'.$search.'%');
        }
        $produits = $produitsQuery->orderBy('nom')->get();


        // Gestion multi-paniers par table
        $tableCourante = $request->get('table_id');
        $paniers = session()->get('paniers', []);
        $panier = $tableCourante ? ($paniers[$tableCourante] ?? []) : [];
        $produitsPanier = \App\Models\Produit::whereIn('id', array_keys($panier))->get();

        // Récupérer les clients de l'entreprise liée au PDV
        $clients = $pointDeVente->entreprise->clients;
        // Récupérer les serveuses (utilisateurs avec role 'serveuse') de l'entreprise
        $serveuses = $pointDeVente->entreprise->users()->where('role', 'serveuse')->get();

        // Récupérer les tables associées au point de vente (via les salles)
        $tables = \App\Models\TableResto::whereIn('salle_id', $pointDeVente->salles->pluck('id'))->get();

        return view('vente.catalogue', [
            'pointDeVente' => $pointDeVente,
            'categories' => $categories,
            'categorieActive' => $categorieActive,
            'search' => $search,
            'produits' => $produits,
            'panier' => $panier,
            'produitsPanier' => $produitsPanier,
            'clients' => $clients,
            'serveuses' => $serveuses,
            'tables' => $tables,
            'tableCourante' => $tableCourante,
        ]);
    }


    public function afficherPanier(Request $request, $pointDeVenteId)
    {
        $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);
        $panier = session()->get('panier', []);
        // Récupérer les infos produits pour affichage
        $produits = \App\Models\Produit::whereIn('id', array_keys($panier))->get();
        // Récupérer les clients de l'entreprise liée au PDV
        $clients = $pointDeVente->entreprise->clients;
        // Récupérer les serveuses (utilisateurs avec role 'serveuse') de l'entreprise
        $serveuses = $pointDeVente->entreprise->users()->where('role', 'serveuse')->get();
        return view('vente.panier', [
            'pointDeVente' => $pointDeVente,
            'panier' => $panier,
            'produits' => $produits,
            'clients' => $clients,
            'serveuses' => $serveuses,
        ]);
    }
    
      /**
     * Ajoute un produit au panier (stockage en session pour l'instant)
     */
    public function ajouterAuPanier(Request $request, $produitId)
    {
        // Gestion multi-paniers par table
        $tableId = $request->get('table_id');
        if (!$tableId) {
            return response()->json(['error' => 'Aucune table sélectionnée'], 422);
        }
        $paniers = session()->get('paniers', []);
        $panier = $paniers[$tableId] ?? [];
        // Incrémenter la quantité si déjà présent, sinon ajouter
        if (isset($panier[$produitId])) {
            $panier[$produitId]['quantite'] += 1;
        } else {
            $panier[$produitId] = [
                'produit_id' => $produitId,
                'quantite' => 1
            ];
        }
        $paniers[$tableId] = $panier;
        session(['paniers' => $paniers]);
        return response()->json(['success' => true, 'panier' => $panier]);
    }

    // Affiche la page de vente pour un point de vente donné
    public function continuer($id)
    {
        $pointDeVente = PointDeVente::findOrFail($id);
        // On récupère la première salle associée au point de vente
        $salle = $pointDeVente->salles()->with('tables')->first();
        $entreprise = $pointDeVente->entreprise;
        if ($salle) {
            // Redirection directe vers le plan de salle en mode vente (aucune modification possible)
            return redirect()->route('salle.plan.vente', [
                'entreprise' => $entreprise->id,
                'salle' => $salle->id,
                'point_de_vente_id' => $pointDeVente->id
            ]);
        }
        // Sinon, on garde le comportement classique (pas de salle)
        return view('vente.continuer', compact('pointDeVente'));
    }

    // Ouvre un point de vente (changement d'état, traçabilité)
    public function ouvrir($id)
    {
        $pointDeVente = PointDeVente::findOrFail($id);
        if ($pointDeVente->etat === 'ouvert') {
            return redirect()->back()->with('error', 'Le point de vente est déjà ouvert.');
        }
        DB::transaction(function () use ($pointDeVente) {
            $pointDeVente->update(['etat' => 'ouvert']);
            Historiquepdv::create([
                'point_de_vente_id' => $pointDeVente->id,
                'user_id' => Auth::id(),
                'etat' => 'ouvert',
            ]);
        });
        // Redirection directe vers le plan de la première salle du point de vente
        $salle = $pointDeVente->salles()->first();
        if ($salle) {
            return redirect()->route('salle.plan.vente', [
                'entreprise' => $pointDeVente->entreprise_id,
                'salle' => $salle->id,
                'point_de_vente_id' => $pointDeVente->id
            ])->with('success', 'Point de vente ouvert. Sélectionnez une table.');
        }
        // Si pas de salle, fallback sur la page continuer
        return redirect()->route('vente.continuer', $pointDeVente->id)->with('success', 'Point de vente ouvert.');
    }

    // Ferme un point de vente (changement d'état, traçabilité)
    public function fermer($id)
    {
        $pointDeVente = PointDeVente::findOrFail($id);
        if ($pointDeVente->etat === 'ferme') {
            return redirect()->back()->with('error', 'Le point de vente est déjà fermé.');
        }
        DB::transaction(function () use ($pointDeVente) {
            $pointDeVente->update(['etat' => 'ferme']);
            Historiquepdv::create([
                'point_de_vente_id' => $pointDeVente->id,
                'user_id' => Auth::id(),
                'etat' => 'ferme',
            ]);
        });
        return redirect()->route('pointsDeVente.show', $pointDeVente->entreprise_id)->with('success', 'Point de vente fermé.');
    }

    public function setClient(\Illuminate\Http\Request $request)
    {
        $tableId = $request->get('table_id');
        if (!$tableId) {
            return response()->json(['error' => 'Aucune table sélectionnée'], 422);
        }
        $paniers = session()->get('paniers', []);
        $panier = $paniers[$tableId] ?? [];
        $panier['client_id'] = $request->client_id;
        $paniers[$tableId] = $panier;
        session(['paniers' => $paniers]);
        return response()->json(['ok' => true]);
    }

    public function setServeuse(\Illuminate\Http\Request $request)
    {
        $tableId = $request->get('table_id');
        if (!$tableId) {
            return response()->json(['error' => 'Aucune table sélectionnée'], 422);
        }
        $paniers = session()->get('paniers', []);
        $panier = $paniers[$tableId] ?? [];
        $panier['serveuse_id'] = $request->serveuse_id;
        $paniers[$tableId] = $panier;
        session(['paniers' => $paniers]);
        return response()->json(['ok' => true]);
    }
}
