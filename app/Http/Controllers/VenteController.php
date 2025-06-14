<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PointDeVente;
use App\Models\Historiquepdv;
use App\Models\Panier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VenteController extends Controller
{
    /**
     * Affichage du catalogue produits pour la vente (PDV)
     */
    public function catalogue(Request $request, $pointDeVenteId)
    {
        try {
            $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);
            $categories = $pointDeVente->categories;
            $categorieActive = $request->get('categorie');
            $search = $request->get('search');

            $produitsQuery = \App\Models\Produit::query();
            if ($categorieActive) {
                $produitsQuery->where('categorie_id', $categorieActive);
            } else {
                $produitsQuery->whereIn('categorie_id', $categories->pluck('id'));
            }
            if ($search) {
                $produitsQuery->where('nom', 'like', '%'.$search.'%');
            }
            $produits = $produitsQuery->orderBy('nom')->get();

            // Gestion multi-paniers par table (NOUVELLE LOGIQUE)
            $tableCourante = $request->get('table_id');
            $produitsPanier = [];
            if ($tableCourante) {
                $panier = \App\Models\Panier::where('table_id', $tableCourante)
                    ->where('point_de_vente_id', $pointDeVenteId)
                    ->first();
                if ($panier) {
                    $panier->load('produits');
                    $produitsPanier = $panier->produits->map(function($prod) {
                        return [
                            'id' => $prod->id,
                            'nom' => $prod->nom,
                            'qte' => $prod->pivot->quantite,
                            'prix' => $prod->prix_vente,
                            'image' => $prod->image ? asset('storage/'.$prod->image) : null,
                            'cat_id' => $prod->categorie_id,
                        ];
                    })->values()->toArray();
                }
            }

            // Création automatique du panier vide si besoin
            if ($tableCourante) {
                $panier = \App\Models\Panier::firstOrCreate([
                    'table_id' => $tableCourante,
                    'point_de_vente_id' => $pointDeVenteId,
                ], [
                    'opened_by' => \Auth::id(),
                ]);
                $panier->load('produits');
                $produitsPanier = $panier->produits->map(function($prod) {
                    return [
                        'id' => $prod->id,
                        'nom' => $prod->nom,
                        'qte' => $prod->pivot->quantite,
                        'prix' => $prod->prix_vente,
                        'image' => $prod->image ? asset('storage/'.$prod->image) : null,
                        'cat_id' => $prod->categorie_id,
                    ];
                })->values()->toArray();
            }

            $clients = $pointDeVente->entreprise->clients;
            $serveuses = $pointDeVente->entreprise->users()->where('role', 'serveuse')->get();
            $tables = \App\Models\TableResto::whereIn('salle_id', $pointDeVente->salles->pluck('id'))->get();

            return view('vente.catalogue', [
                'pointDeVente' => $pointDeVente,
                'categories' => $categories,
                'categorieActive' => $categorieActive,
                'search' => $search,
                'produits' => $produits,
                'produitsPanier' => $produitsPanier,
                'clients' => $clients,
                'serveuses' => $serveuses,
                'tables' => $tables,
                'tableCourante' => $tableCourante,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Erreur catalogue vente: '.$e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Erreur serveur: '.$e->getMessage());
        }
    }


    public function afficherPanier(Request $request, $pointDeVenteId)
    {
        try {
            $pointDeVente = PointDeVente::findOrFail($pointDeVenteId);
            $tableCourante = $request->get('table_id');
            $produitsPanier = [];
            if ($tableCourante) {
                $panier = \App\Models\Panier::where('table_id', $tableCourante)
                    ->where('point_de_vente_id', $pointDeVenteId)
                    ->first();
                if ($panier) {
                    $panier->load('produits');
                    $produitsPanier = $panier->produits->map(function($prod) {
                        return [
                            'id' => $prod->id,
                            'nom' => $prod->nom,
                            'qte' => $prod->pivot->quantite,
                            'prix' => $prod->prix_vente,
                            'image' => $prod->image ? asset('storage/'.$prod->image) : null,
                            'cat_id' => $prod->categorie_id,
                        ];
                    })->values()->toArray();
                }
            }
            $clients = $pointDeVente->entreprise->clients;
            $serveuses = $pointDeVente->entreprise->users()->where('role', 'serveuse')->get();
            return view('vente.panier', [
                'pointDeVente' => $pointDeVente,
                'produitsPanier' => $produitsPanier,
                'clients' => $clients,
                'serveuses' => $serveuses,
                'tableCourante' => $tableCourante,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Erreur afficherPanier: '.$e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Erreur serveur: '.$e->getMessage());
        }
    }
    
      /**
     * Ajoute un produit au panier (stockage en session pour l'instant)
     */
    public function ajouterAuPanier(Request $request, $produitId)
    {
        try {
            $tableId = $request->get('table_id');
            $pointDeVenteId = $request->get('point_de_vente_id');
            $clientId = $request->get('client_id');
            $serveuseId = $request->get('serveuse_id');
            $openedBy = \Auth::id();
            if (!$tableId || !$pointDeVenteId) {
                return response()->json(['error' => 'Aucune table ou point de vente sélectionné'], 422);
            }

            // 1. Récupérer ou créer le panier pour la table et le point de vente
            $panier = \App\Models\Panier::firstOrCreate(
                [
                    'table_id' => $tableId,
                    'point_de_vente_id' => $pointDeVenteId,
                ],
                [
                    'client_id' => $clientId,
                    'serveuse_id' => $serveuseId,
                    'opened_by' => $openedBy,
                ]
            );
            // Si le panier existait déjà mais les champs sont vides, on les remplit si possible
            $updated = false;
            if (!$panier->opened_by && $openedBy) { $panier->opened_by = $openedBy; $updated = true; }
            if (!$panier->client_id && $clientId) { $panier->client_id = $clientId; $updated = true; }
            if (!$panier->serveuse_id && $serveuseId) { $panier->serveuse_id = $serveuseId; $updated = true; }
            if ($updated) $panier->save();

            // 2. Vérifier si le produit est déjà dans le panier
            $existant = $panier->produits()->where('produit_id', $produitId)->first();
            if ($existant) {
                $nouvelleQte = $existant->pivot->quantite + 1;
                $panier->produits()->updateExistingPivot($produitId, ['quantite' => $nouvelleQte]);
            } else {
                $panier->produits()->attach($produitId, ['quantite' => 1]);
            }

            // 3. Retourner le panier actualisé (structure attendue par le JS/vue)
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

            return response()->json([
                'success' => true,
                'panier' => $panierArray
            ]);
        } catch (\Throwable $e) {
            \Log::error('Erreur ajout panier: '.$e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'error' => 'Erreur serveur: '.$e->getMessage()
            ], 500);
        }
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
