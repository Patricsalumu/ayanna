<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PointDeVente;
use App\Models\Historiquepdv;
use App\Models\Panier;
use App\Models\Commande;
use App\Models\Vente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            $panier = null;
            Log::debug('[CATALOGUE] table_id re├ºu', ['table_id' => $tableCourante, 'point_de_vente_id' => $pointDeVenteId]);
            if ($tableCourante) {
                $panier = Panier::where('table_id', $tableCourante)
                    ->where('status','en_cours')
                    ->first();
                Log::debug('[CATALOGUE] Panier trouv├® ?', ['panier_id' => $panier ? $panier->id : null]);
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
                } else {
                    Log::debug('[CATALOGUE] Cr├®ation d\'un nouveau panier pour la table', ['table_id' => $tableCourante, 'point_de_vente_id' => $pointDeVenteId]);
                    $panier = Panier::create([
                        'table_id' => $tableCourante,
                        'status' => 'en_cours',
                        'point_de_vente_id' => $pointDeVenteId,
                        'opened_by' => Auth::id(),
                    ]);
                    $panier->load('produits');
                }
            }

            $clients = $pointDeVente->entreprise->clients;
            $serveuses = $pointDeVente->entreprise->users()->where('role', 'serveuse')->get();
            $tables = \App\Models\TableResto::whereIn('salle_id', $pointDeVente->salles->pluck('id'))->get();

            $client_id = $panier ? $panier->client_id : '';
            $serveuse_id = $panier ? $panier->serveuse_id : '';

            // R├®cup├®rer les modes de paiement actifs pour l'entreprise
            $modesPaiement = \App\Models\ModePaiement::where('entreprise_id', $pointDeVente->entreprise_id)
                ->where('actif', true)
                ->get();

            // G├®n├®ration des tableaux pour Alpine.js (JS)
            $produitsArray = $produits->map(function($p){
                return [
                    'id' => $p->id,
                    'nom' => $p->nom,
                    'prix' => $p->prix_vente,
                    'image' => $p->image ? asset('storage/'.$p->image) : null,
                    'cat_id' => $p->categorie_id
                ];
            })->toArray();

            $clientsArray = $clients->map(function($c){
                return [
                    'id' => $c->id,
                    'nom' => $c->nom,
                ];
            })->toArray();

            $serveusesArray = $serveuses->map(function($s){
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                ];
            })->toArray();

            $modesPaiementArray = $modesPaiement->map(function($m){
                return [
                    'id' => $m->id,
                    'nom' => $m->nom,
                ];
            })->toArray();

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
                'client_id' => $client_id,
                'serveuse_id' => $serveuse_id,
                'panier' => $panier ?? null,
                'modesPaiement' => $modesPaiement,
                'produitsArray' => $produitsArray,
                'clientsArray' => $clientsArray,
                'serveusesArray' => $serveusesArray,
                'modesPaiementArray' => $modesPaiementArray,
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur catalogue vente: '.$e->getMessage(), ['exception' => $e]);
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
                    ->where('status', 'en_cours')
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
            Log::error('Erreur afficherPanier: '.$e->getMessage(), ['exception' => $e]);
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
            $openedBy = Auth::id();
            if (!$tableId || !$pointDeVenteId) {
                return response()->json(['error' => 'Aucune table ou point de vente s├®lectionn├®'], 422);
            }

            // 1. R├®cup├®rer ou cr├®er le panier pour la table et le point de vente
            $panier = \App\Models\Panier::firstOrCreate(
                [
                    'table_id' => $tableId,
                    'status' => 'en_cours',
                ],
                [
                    'point_de_vente_id' => $pointDeVenteId,
                    'client_id' => $clientId,
                    'serveuse_id' => $serveuseId,
                    'opened_by' => $openedBy,
                ]
            );
            // Si le panier existait d├®j├á mais les champs sont vides, on les remplit si possible
            $updated = false;
            if (!$panier->opened_by && $openedBy) { $panier->opened_by = $openedBy; $updated = true; }
            if (!$panier->client_id && $clientId) { $panier->client_id = $clientId; $updated = true; }
            if (!$panier->serveuse_id && $serveuseId) { $panier->serveuse_id = $serveuseId; $updated = true; }
            if ($updated) $panier->save();

            // 2. V├®rifier si le produit est d├®j├á dans le panier
            $existant = $panier->produits()->where('produit_id', $produitId)->first();
            if ($existant) {
                $nouvelleQte = $existant->pivot->quantite + 1;
                $panier->produits()->updateExistingPivot($produitId, ['quantite' => $nouvelleQte]);
            } else {
                $panier->produits()->attach($produitId, ['quantite' => 1]);
            }

            // 3. Retourner le panier actualis├® (structure attendue par le JS/vue)
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
            Log::error('Erreur ajout panier: '.$e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'error' => 'Erreur serveur: '.$e->getMessage()
            ], 500);
        }
    }

    // Affiche la page de vente pour un point de vente donn├®
    public function continuer($id)
    {
        $pointDeVente = PointDeVente::findOrFail($id);
        // On r├®cup├¿re la premi├¿re salle associ├®e au point de vente
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

    // Ouvre un point de vente (affiche la fiche d'ouverture du stock, sans changer l'├®tat)
    public function ouvrir($id)
    {
        $pointDeVente = PointDeVente::findOrFail($id);
        if ($pointDeVente->etat === 'ouvert') {
            return redirect()->back()->with('error', 'Le point de vente est d├®j├á ouvert.');
        }
        // NE PAS changer l'├®tat ici, juste afficher la fiche d'ouverture du stock
        return redirect()->route('stock_journalier.ouverture', ['pointDeVente' => $pointDeVente->id])
            ->with('success', 'Veuillez valider la fiche d\'ouverture du stock.');
    }

    // Ferme un point de vente (changement d'├®tat, tra├ºabilit├®)
    public function fermer($id)
    {
        $pointDeVente = PointDeVente::findOrFail($id);
        if ($pointDeVente->etat === 'ferme') {
            return redirect()->back()->with('error', 'Le point de vente est d├®j├á ferm├®.');
        }
        DB::transaction(function () use ($pointDeVente) {
            $pointDeVente->update(['etat' => 'ferme']);
            Historiquepdv::create([
                'point_de_vente_id' => $pointDeVente->id,
                'user_id' => Auth::id(),
                'etat' => 'ferme',
            ]);
        });
        return redirect()->route('pointsDeVente.show', [$pointDeVente->entreprise_id, $pointDeVente->id])->with('success', 'Point de vente ferm├®.');
    }

    // D├®finit le client pour un panier sp├®cifique
    public function setClient(\Illuminate\Http\Request $request)
    {
        $tableId = $request->get('table_id');
        if (!$tableId) {
            return response()->json(['error' => 'Aucune table s├®lectionn├®e'], 422);
        }
        $paniers = session()->get('paniers', []);
       
        session(['paniers' => $paniers]);
        return response()->json(['ok' => true]);
    }
   
    // D├®finit la serveuse pour un panier sp├®cifique
    public function setServeuse(\Illuminate\Http\Request $request)
    {
        $tableId = $request->get('table_id');
        if (!$tableId) {
            return response()->json(['error' => 'Aucune table s├®lectionn├®e'], 422);
        }
        $paniers = session()->get('paniers', []);
        $panier = $paniers[$tableId] ?? [];
        $panier['serveuse_id'] = $request->serveuse_id;
        $paniers[$tableId] = $panier;
        session(['paniers' => $paniers]);
        return response()->json(['ok' => true]);
    }
     // Valide le paiement d'un panier et cr├®e une commande
    public function valider(Request $request)
    {
        $data = $request->all();
        Log::info('Payload re├ºu pour validation', $data);

        if (empty($data['panier_id'])) {
            return response()->json(['success' => false, 'error' => 'Aucun panier_id fourni.'], 400);
        }
        
        // V├®rification sp├®cifique pour le paiement par compte client
        if (
            isset($data['mode_paiement']) && strtolower($data['mode_paiement']) === 'compte_client'
        ) {
            $panier = \App\Models\Panier::find($data['panier_id']);
            if (!$panier || !$panier->client_id || !$panier->serveuse_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Pour un paiement par compte client, vous devez s├®lectionner un client et une serveuse.'
                ], 422);
            } 
        }
        // D├®terminer le statut selon le mode de paiement
        $statut = 'valid├®';
        if (isset($data['mode_paiement'])) {
            $mode = strtolower($data['mode_paiement']);
            if ($mode === 'esp├¿ces' || $mode === 'especes' || $mode === 'cash') {
                $statut = 'cash';
            } elseif ($mode === 'compte_client' || $mode === 'compte client' || $mode === 'credit') {
                $statut = 'credit';
            } elseif ($mode === 'mobile_money' || $mode === 'mobile money') {
                $statut = 'mobilemoney';
            }
        }
        $commande = new Commande();
        $commande->panier_id = $data['panier_id'];
        $commande->mode_paiement = $data['mode_paiement'];
        $commande->statut = $statut;
        $commande->created_at = now(); // Ajout manuel de la date de cr├®ation
        $commande->save();

        // Mettre ├á jour le statut du panier ├á 'valide' apr├¿s paiement
        $panier = \App\Models\Panier::find($data['panier_id']);
        if ($panier) {
            $panier->status = 'valid├®';
            $panier->save();
            // MAJ quantit├® vendue dans le stock journalier
            $this->majQuantiteVendueStock($panier);
            // Cr├®er un nouveau panier vide pour la m├¬me table (statut en_cours)
            $nouveauPanier = \App\Models\Panier::create([
                'table_id' => $panier->table_id,
                'status' => 'en_cours',
                'point_de_vente_id' => $panier->point_de_vente_id,
                'opened_by' => Auth::id(),
            ]);
            return response()->json([
                'success' => true,
                'commande_id' => $commande->id,
                'nouveau_panier_id' => $nouveauPanier->id
            ]);
        }
        return response()->json(['success' => true, 'commande_id' => $commande->id]);
    }

    /**
     * Met ├á jour la quantit├® vendue dans le stock journalier ├á la validation d'un panier
     */
    private function majQuantiteVendueStock($panier)
    {
        if (!$panier) return;
        $pointDeVenteId = $panier->point_de_vente_id;
        $date = now()->toDateString();
        // R├®cup├®rer la session en cours (la plus r├®cente pour ce point de vente et ce jour)
        $session = \App\Models\StockJournalier::where('point_de_vente_id', $pointDeVenteId)
            ->where('date', $date)
            ->orderByDesc('session')
            ->value('session');
        if (!$session) return;
        foreach ($panier->produits as $produit) {
            $stock = \App\Models\StockJournalier::where('point_de_vente_id', $pointDeVenteId)
                ->where('date', $date)
                ->where('session', $session)
                ->where('produit_id', $produit->id)
                ->first();
            if ($stock) {
                $qteVendue = $produit->pivot->quantite;
                $stock->quantite_vendue = ($stock->quantite_vendue ?? 0) + $qteVendue;
                // Mettre ├á jour la quantit├® rest├®e
                $q_total = ($stock->quantite_initiale ?? 0) + ($stock->quantite_ajoutee ?? 0);
                $stock->quantite_reste = $q_total - $stock->quantite_vendue;
                $stock->save();
            }
        }
    }

    // Affiche la liste des cr├®ances (commandes avec paiement par compte client)
    public function creances(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? ($user->entreprise->id ?? null);
        $filtre = $request->get('filtre', 'jour'); // 'jour' ou 'toutes'
        
        $query = Commande::with(['panier', 'panier.client', 'panier.serveuse', 'panier.tableResto', 'panier.pointDeVente', 'paiements'])
            ->where('mode_paiement', 'compte_client')
            ->whereHas('panier.tableResto.salle', function($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            });
            
        if ($filtre === 'jour') {
            $query->whereDate('created_at', now()->toDateString());
        }
        
        $creances = $query->orderByDesc('created_at')->get();
        
        return view('creances.liste', compact('creances', 'filtre'));
    }

    public function confirmerCreance($commandeId)
    {
        $commande = \App\Models\Commande::findOrFail($commandeId);
        $commande->statut = 'pay├®';
        $commande->save();
        return redirect()->back()->with('success', 'Cr├®ance confirm├®e comme pay├®e.');
    }

    public function enregistrerPaiement(Request $request, $commandeId)
    {
        try {
            Log::info('D├®but enregistrement paiement', [
                'commande_id' => $commandeId,
                'request_data' => $request->all()
            ]);

            $request->validate([
                'montant' => 'required|numeric|min:0.01',
                'mode' => 'required|string',
                'notes' => 'nullable|string|max:500'
            ]);

            $commande = Commande::with(['panier.produits', 'paiements'])->findOrFail($commandeId);
            
            Log::info('Commande trouv├®e', ['commande' => $commande->toArray()]);
            
            // Calculer le montant total de la commande
            $montantTotal = $commande->montant ?? $commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente);
            
            // Calculer le montant d├®j├á pay├®
            $montantDejaPaye = $commande->paiements->sum('montant');
            
            // Calculer le montant restant
            $montantRestant = $montantTotal - $montantDejaPaye;
            
            Log::info('Calculs paiement', [
                'montant_total' => $montantTotal,
                'montant_deja_paye' => $montantDejaPaye,
                'montant_restant' => $montantRestant,
                'montant_demande' => $request->montant
            ]);
            
            // V├®rifier que le montant ├á payer ne d├®passe pas le restant
            $montantAPayer = min($request->montant, $montantRestant);
            
            // Calculer le nouveau montant restant
            $nouveauMontantRestant = $montantRestant - $montantAPayer;
            
            // D├®terminer le compte selon le r├┤le de l'utilisateur
            $user = Auth::user();
            $compteId = null;
            
            if ($user->role === 'admin') {
                // Pour admin : compte caisse directement
                $compte = \App\Models\Compte::where('nom', 'LIKE', '%caisse%')
                    ->where('entreprise_id', $commande->panier->pointDeVente->entreprise_id)
                    ->first();
            } else {
                // Pour comptable/comptoir : compte comptoir
                $compte = \App\Models\Compte::where('nom', 'LIKE', '%comptoir%')
                    ->where('entreprise_id', $commande->panier->pointDeVente->entreprise_id)
                    ->first();
            }
            
            // Si aucun compte sp├®cifique trouv├®, prendre le premier compte de l'entreprise
            if (!$compte) {
                $compte = \App\Models\Compte::where('entreprise_id', $commande->panier->pointDeVente->entreprise_id)->first();
            }
            
            $compteId = $compte ? $compte->id : null;
            
            Log::info('Compte s├®lectionn├®', [
                'user_role' => $user->role,
                'compte_id' => $compteId,
                'compte_nom' => $compte ? $compte->nom : 'Aucun'
            ]);
            
            // Cr├®er le paiement
            $paiement = \App\Models\Paiement::create([
                'compte_id' => $compteId,
                'commande_id' => $commande->id,
                'montant' => $montantAPayer,
                'montant_restant' => $nouveauMontantRestant,
                'mode' => $request->mode,
                'date_paiement' => now()->toDateString(),
                'notes' => $request->notes,
                'est_solde' => $nouveauMontantRestant <= 0,
                'user_id' => Auth::id(),
                'statut' => 'valid├®'
            ]);
            
            Log::info('Paiement cr├®├®', ['paiement' => $paiement->toArray()]);
            
            // Si compl├¿tement pay├®, marquer la commande comme pay├®e
            if ($nouveauMontantRestant <= 0) {
                $commande->statut = 'pay├®';
                $commande->save();
                Log::info('Commande marqu├®e comme pay├®e');
            }
            
            return response()->json([
                'success' => true,
                'message' => $nouveauMontantRestant <= 0 ? 'Cr├®ance totalement sold├®e !' : 'Paiement partiel enregistr├®',
                'montant_paye' => $montantAPayer,
                'montant_restant' => $nouveauMontantRestant,
                'est_solde' => $nouveauMontantRestant <= 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement du paiement: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function historiqueCreance($commandeId)
    {
        $commande = \App\Models\Commande::with(['panier.client', 'panier.serveuse', 'panier.tableResto', 'panier.produits', 'paiements.user'])
            ->findOrFail($commandeId);
        
        return view('creances.historique', compact('commande'));
    }

    public function imprimerCreance($commandeId)
    {
        $commande = \App\Models\Commande::with(['panier.client', 'panier.serveuse', 'panier.tableResto', 'panier.produits', 'panier.pointDeVente.entreprise', 'paiements'])
            ->findOrFail($commandeId);
        
        return view('creances.facture', compact('commande'));
    }
}
