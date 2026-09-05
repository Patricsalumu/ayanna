<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Panier;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\PointDeVente;
use App\Models\StockJournalier;
use App\Models\Historiquepdv;
use App\Services\PermissionService;
use Barryvdh\DomPDF\Facade\Pdf;

class PanierController extends Controller
{
    public function __construct(protected PermissionService $permissionService)
    {
    }

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
        if (!$this->permissionService->canAddProductsToTable(Auth::user())) {
            return response()->json([
                'success' => false,
                'error' => 'Le caissier ne peut pas ajouter de produits dans une table.',
            ], 403);
        }

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
        if (!$this->permissionService->canAddProductsToTable(Auth::user())) {
            return response()->json([
                'success' => false,
                'error' => "Le caissier ne peut pas modifier les produits d'une table.",
            ], 403);
        }

        try {
            $table_id = $request->input('table_id');
            $quantite = max(0, (int) $request->input('quantite', 0));

            $panier = Panier::where('table_id', $table_id)
                ->where('status', 'en_cours')
                ->first();

            if (!$panier) return response()->json(['error' => 'Panier non trouvé'], 404);

            $existant = $panier->produits()->where('produit_id', $produit_id)->first();
            $ancienneQuantite = $existant?->pivot?->quantite ?? 0;
            if ($this->doitExigerMotDePasseAdmin($ancienneQuantite, $quantite) && !$this->verifierMotDePasseAdmin($request)) {
                return response()->json(['success' => false, 'error' => 'Mot de passe administrateur requis pour diminuer ou supprimer un produit.'], 403);
            }

            if ($quantite <= 0) {
                if ($existant) {
                    $panier->produits()->detach($produit_id);
                }
            } elseif ($existant) {
                $panier->produits()->updateExistingPivot($produit_id, ['quantite' => $quantite]);
            } else {
                $produit = \App\Models\Produit::find($produit_id);
                $panier->produits()->attach($produit_id, ['quantite' => $quantite, 'prix' => $produit?->prix_vente ?? 0]);
            }

            $panier->load('produits');
            $panierArray = $panier->produits->map(function($prod){
                return [
                    'id' => $prod->id,
                    'nom' => $prod->nom,
                    'prix' => $prod->pivot->prix ?? $prod->prix_vente,
                    'qte' => $prod->pivot->quantite,
                    'image' => $prod->image ? asset('storage/'.$prod->image) : null,
                    'cat_id' => $prod->categorie_id,
                ];
            })->filter(fn($item) => (int) ($item['qte'] ?? 0) > 0)->values()->toArray();

            return response()->json(['success' => true, 'panier' => $panierArray]);
        } catch (\Throwable $e) {
            Log::error('Erreur modifierProduit panier: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'error' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    // Supprimer un produit du panier
    public function supprimerProduit(Request $request, $produit_id)
    {
        if (!$this->permissionService->canAddProductsToTable(Auth::user())) {
            return response()->json([
                'success' => false,
                'error' => "Le caissier ne peut pas supprimer les produits d'une table.",
            ], 403);
        }

        $table_id = $request->input('table_id');
        $panier = Panier::where('table_id', $table_id)
            ->where('status', 'en_cours')
            ->first();

        if (!$panier) return response()->json(['error' => 'Panier non trouvé'], 404);

        if ($this->roleNePeutPasDiminuerPanier() && !$this->verifierMotDePasseAdmin($request)) {
            return response()->json(['success' => false, 'error' => 'Mot de passe administrateur requis pour supprimer un produit.'], 403);
        }

        $existant = $panier->produits()->where('produit_id', $produit_id)->first();
        if ($existant) {
            $panier->produits()->detach($produit_id);
        }

        $panier->load('produits');
        $panierArray = $panier->produits
            ->map(function($prod){
                return [
                    'id' => $prod->id,
                    'nom' => $prod->nom,
                    'prix' => $prod->pivot->prix ?? $prod->prix_vente,
                    'qte' => $prod->pivot->quantite,
                    'image' => $prod->image ? asset('storage/'.$prod->image) : null,
                    'cat_id' => $prod->categorie_id,
                ];
            })
            ->filter(fn($item) => (int) ($item['qte'] ?? 0) > 0)
            ->values()
            ->toArray();

        return response()->json(['success' => true, 'panier' => $panierArray]);
    }

    public function verifierMotDePasseAdminPourAction(Request $request)
    {
        if (!$this->roleNePeutPasDiminuerPanier()) {
            return response()->json(['success' => true]);
        }

        $password = (string) $request->input('password_admin', '');
        if ($password === '') {
            return response()->json(['success' => false, 'error' => 'Mot de passe administrateur requis.'], 422);
        }

        $user = Auth::user();
        $adminRoles = ['admin', 'super_admin', 'administrateur'];
        $adminUsers = User::where('entreprise_id', $user?->entreprise_id)
            ->get()
            ->filter(function ($adminUser) use ($adminRoles) {
                return in_array(strtolower((string) ($adminUser->role ?? '')), $adminRoles, true);
            });

        foreach ($adminUsers as $adminUser) {
            if ($adminUser->id === $user?->id || Hash::check($password, $adminUser->password)) {
                return response()->json(['success' => true]);
            }
        }

        return response()->json(['success' => false, 'error' => 'Mot de passe administrateur incorrect.'], 403);
    }

    private function doitExigerMotDePasseAdmin(?int $ancienneQuantite, ?int $nouvelleQuantite): bool
    {
        if (!$this->roleNePeutPasDiminuerPanier()) {
            return false;
        }

        if ($ancienneQuantite === null || $nouvelleQuantite === null) {
            return false;
        }

        return $nouvelleQuantite < $ancienneQuantite || $nouvelleQuantite === 0;
    }

    private function verifierMotDePasseAdmin(Request $request): bool
    {
        if (!$this->roleNePeutPasDiminuerPanier()) {
            return true;
        }

        $password = (string) $request->input('password_admin', '');
        if ($password === '') {
            return false;
        }

        $user = Auth::user();
        $adminRoles = ['admin', 'super_admin', 'administrateur'];
        $adminUsers = User::where('entreprise_id', $user?->entreprise_id)
            ->get()
            ->filter(function ($adminUser) use ($adminRoles) {
                return in_array(strtolower((string) ($adminUser->role ?? '')), $adminRoles, true);
            });

        foreach ($adminUsers as $adminUser) {
            if ($adminUser->id === $user?->id || Hash::check($password, $adminUser->password)) {
                return true;
            }
        }

        return false;
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
            if (!$this->permissionService->canEditServeuseAssignment(Auth::user())) {
                return response()->json(['success' => false, 'error' => 'Vous n\'êtes pas autorisé à modifier la serveuse.'], 403);
            }

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
     * Affiche tous les paniers de la session (ou du jour si sélection "Toutes")
     */
    public function paniersDuJour(Request $request)
    {
        $data = $this->getPaniersDuJourData($request);

        return view('paniers.jour', $data);
    }

    /**
     * Exporte les paniers de la session selectionnee en PDF.
     */
    public function exportPaniersDuJourPdf(Request $request)
    {
        $data = $this->getPaniersDuJourData($request);
        $data['entreprise'] = Auth::user()?->entreprise;

        $fileName = 'paniers_du_jour_'.now()->format('Ymd_His').'.pdf';

        return Pdf::loadView('paniers.jour-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($fileName);
    }

    /**
     * Prepare les donnees de la page et du PDF des paniers du jour.
     */
    private function getPaniersDuJourData(Request $request): array
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? ($user->entreprise->id ?? null);
        $today = now()->toDateString();
        $selectedSession = $request->get('session', null);
        $selectedSessionFrom = $request->get('session_from', null);
        $selectedSessionTo = $request->get('session_to', null);
        $selectedPaymentType = $request->get('payment_type', null); // 'all'|'credit'|'cash' (or null)
        $searchTerm = trim((string) $request->get('search', ''));

        $pointDeVenteIds = PointDeVente::where('entreprise_id', $entrepriseId)->pluck('id');
        $pointDeVenteId = $request->integer('point_de_vente_id') ?: session('point_de_vente_id');
        if ($pointDeVenteId && $pointDeVenteIds->contains((int) $pointDeVenteId)) {
            $pointDeVenteIds = collect([(int) $pointDeVenteId]);
        } else {
            $pointDeVenteId = null;
        }

        // Les sessions proposées appartiennent uniquement au point de vente actif.
        $sessionGroups = StockJournalier::with('pointDeVente')
            ->whereIn('point_de_vente_id', $pointDeVenteIds)
            ->orderByDesc('session')
            ->get()
            ->groupBy('session');

        $sessions = $sessionGroups->map(function ($stocks, $session) {
            $first = $stocks->sortBy('validated_at')->first();
            return (object) [
                'session' => $session,
                'point_de_vente_id' => $first->point_de_vente_id,
                'point_de_vente_nom' => $first->pointDeVente?->nom ?? 'N/A',
                'validated_at' => $first->validated_at ?? $first->created_at,
                'status' => $first->pointDeVente?->etat === 'ouvert' ? 'ouverte' : 'fermée',
            ];
        })->values();

        if (!$selectedSession && !$selectedSessionFrom && !$selectedSessionTo && $sessions->count() > 0) {
            $selectedSession = $sessions->first()->session;
        }

        $paniersQuery = Panier::whereHas('tableResto.salle', function ($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            });

        if ($pointDeVenteId) {
            $paniersQuery->where('point_de_vente_id', $pointDeVenteId);
        }

        if (in_array($user?->role, ['Serveuse', 'serveuse'], true)) {
            $paniersQuery->where('serveuse_id', $user->id);
        }

        // Filtrage par intervalle de sessions (session_from & session_to) si fournis
        if ($selectedSessionFrom || $selectedSessionTo) {
            $fromInfo = $sessions->firstWhere('session', $selectedSessionFrom);
            $toInfo = $sessions->firstWhere('session', $selectedSessionTo);

            // Déterminer bornes temporelles
            $start = $fromInfo->validated_at ?? null;
            $end = $toInfo->validated_at ?? null;

            if ($start && $end) {
                // s'assurer que start <= end
                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }
                // inclure la fin de session (si fermeture enregistrée pour la session to et même PDV)
                $closedAtTo = null;
                if ($toInfo) {
                    $closedAtTo = Historiquepdv::where('point_de_vente_id', $toInfo->point_de_vente_id)
                        ->where('etat', 'ferme')
                        ->where('opened_at', $toInfo->validated_at)
                        ->value('closed_at');
                }
                $endTimestamp = $closedAtTo ?? ($end ? Carbon::parse($end)->endOfDay() : null);
                if ($endTimestamp) {
                    $paniersQuery = $paniersQuery->whereBetween('created_at', [$start, $endTimestamp]);
                } else {
                    $paniersQuery = $paniersQuery->where('created_at', '>=', $start);
                }
            } elseif ($start) {
                $paniersQuery = $paniersQuery->where('created_at', '>=', $start);
            } elseif ($end) {
                $paniersQuery = $paniersQuery->where('created_at', '<=', $end);
            } else {
                // si aucune info de session valide, filtre par aujourd'hui
                $paniersQuery = $paniersQuery->whereDate('created_at', $today);
            }
        } elseif ($selectedSession && $selectedSession !== 'all') {
            $sessionInfo = $sessions->firstWhere('session', $selectedSession);
            if ($sessionInfo) {
                $paniersQuery = $paniersQuery
                    ->where('point_de_vente_id', $sessionInfo->point_de_vente_id)
                    ->where('created_at', '>=', $sessionInfo->validated_at);

                $closedAt = Historiquepdv::where('point_de_vente_id', $sessionInfo->point_de_vente_id)
                    ->where('etat', 'ferme')
                    ->where('opened_at', $sessionInfo->validated_at)
                    ->value('closed_at');

                if ($closedAt) {
                    $paniersQuery = $paniersQuery->where('created_at', '<=', $closedAt);
                }
            }
        } elseif ($selectedSession === 'all') {
            // Toutes les sessions : pas de filtre de date
        } else {
            $paniersQuery = $paniersQuery->whereDate('created_at', $today);
        }

        if ($searchTerm !== '') {
            $paniersQuery->where(function ($q) use ($searchTerm) {
                $q->whereHas('client', function ($clientQuery) use ($searchTerm) {
                    $clientQuery->where('nom', 'like', "%{$searchTerm}%");
                })->orWhereHas('serveuse', function ($serveuseQuery) use ($searchTerm) {
                    $serveuseQuery->where('name', 'like', "%{$searchTerm}%");
                })->orWhereHas('tableResto.salle', function ($salleQuery) use ($searchTerm) {
                    $salleQuery->where('nom', 'like', "%{$searchTerm}%");
                })->orWhereHas('pointDeVente', function ($pdvQuery) use ($searchTerm) {
                    $pdvQuery->where('nom', 'like', "%{$searchTerm}%");
                });
            });
        }

        $paniers = $paniersQuery
            ->with(['tableResto', 'serveuse', 'client', 'produits', 'pointDeVente', 'commande.paiements'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Si un filtre payment_type est fourni, filtrer la collection en mémoire (valeurs hétérogènes possible)
        if ($selectedPaymentType && $selectedPaymentType !== 'all') {
            $paniers = $paniers->filter(function ($panier) use ($selectedPaymentType) {
                $isCredit = $this->estModeCreditPaiement($panier->commande?->mode_paiement ?? $panier->mode_paiement);
                if ($selectedPaymentType === 'credit') return $isCredit;
                if ($selectedPaymentType === 'cash' || $selectedPaymentType === 'especes' || $selectedPaymentType === 'espace') return !$isCredit;
                return true;
            })->values();
        }

        $paniersActifs = $paniers->reject(fn($panier) => $panier->status === 'annulé');
        $totalPaniers = $paniersActifs->count();

        $totalVente = $paniersActifs->sum(fn($panier) => $this->montantPanierSansRemise($panier));
        $totalRemise = $paniersActifs->sum(fn($panier) => (float) ($panier->total_remise ?? $panier->remise ?? 0));

        $totalCredit = 0.0;
        $totalOffre = 0.0;
        $totalEspeces = 0.0;
        $totalCarte = 0.0;
        $totalMobileMoney = 0.0;

        foreach ($paniersActifs as $panier) {
            $mode = $this->normalizeModePaiement($panier->commande?->mode_paiement ?? $panier->mode_paiement);
            $montantNet = $this->montantPanierAffiche($panier);

            if ($this->estModeCreditPaiement($mode)) {
                $totalCredit += $montantNet;
                continue;
            }

            if ($this->estModeOffrePaiement($mode)) {
                $totalOffre += $montantNet;
                continue;
            }

            if ($this->estModeCartePaiement($mode)) {
                $totalCarte += $montantNet;
                continue;
            }

            if ($this->estModeMobileMoneyPaiement($mode)) {
                $totalMobileMoney += $montantNet;
                continue;
            }

            $totalEspeces += $montantNet;
        }

        $totalPaye = $totalEspeces + $totalCarte + $totalMobileMoney;
        $soldeTheorique = max(0, $totalVente - $totalRemise - $totalCredit - $totalOffre);

        // Alias conservé pour compatibilité avec les vues existantes.
        $totalMontants = $totalVente;

        return compact(
            'paniers',
            'sessions',
            'selectedSession',
            'selectedSessionFrom',
            'selectedSessionTo',
            'selectedPaymentType',
            'searchTerm',
            'totalPaniers',
            'totalMontants',
            'totalVente',
            'totalRemise',
            'totalOffre',
            'totalCredit',
            'totalPaye',
            'totalEspeces',
            'totalCarte',
            'totalMobileMoney',
            'soldeTheorique',
            'pointDeVenteId'
        );
    }

    private function normalizeModePaiement(?string $mode): string
    {
        $mode = trim(strtolower($mode ?? ''));
        $mode = str_replace([' ', '-', 'é', 'è', 'ê', 'à'], ['_', '_', 'e', 'e', 'e', 'a'], $mode);
        return $mode;
    }

    private function estModeCreditPaiement(?string $mode): bool
    {
        $modeNorm = $this->normalizeModePaiement($mode);

        return str_contains($modeNorm, 'compte')
            || in_array($modeNorm, ['credit', 'compteclient', 'compte_client'], true);
    }

    private function estModeCartePaiement(?string $mode): bool
    {
        $modeNorm = $this->normalizeModePaiement($mode);
        return in_array($modeNorm, ['carte', 'card'], true);
    }

    private function estModeOffrePaiement(?string $mode): bool
    {
        $modeNorm = $this->normalizeModePaiement($mode);
        return $modeNorm === 'offre';
    }

    private function estModeMobileMoneyPaiement(?string $mode): bool
    {
        $modeNorm = $this->normalizeModePaiement($mode);
        return in_array($modeNorm, ['mobile_money', 'mobilemoney', 'mobile'], true);
    }

    private function montantPanier(Panier $panier): float
    {
        return (float) $panier->produits->sum(function ($produit) {
            return max(0, $produit->pivot->quantite) * (($produit->pivot->prix ?? $produit->prix_vente) ?? 0);
        });
    }

    private function montantPanierAffiche(Panier $panier): float
    {
        if (($panier->status ?? '') === 'en_cours') {
            return $this->montantPanier($panier);
        }

        return (float) ($panier->total_ttc ?? $this->montantPanier($panier));
    }

    private function montantPanierSansRemise(Panier $panier): float
    {
        $totalTva = (float) ($panier->total_tva ?? 0);

        if (($panier->status ?? '') === 'en_cours') {
            return max(0, $this->montantPanier($panier) + $totalTva);
        }

        if ($panier->total_ht !== null) {
            return max(0, (float) $panier->total_ht + $totalTva);
        }

        return max(0, $this->montantPanier($panier) + $totalTva);
    }

    private function roleNePeutPasDiminuerPanier(): bool
    {
        $role = strtolower((string) (Auth::user()?->role ?? ''));

        return in_array($role, ['comptoiriste', 'serveuse', 'caissier', 'cashier', 'caissier1', 'caissier_1', 'cashier1', 'cashier_1', 'comptoiriste1', 'comptoiriste_1'], true);
    }

    /**
     * Annuler un panier (status = 'annulé')
     */
    public function annuler($id)
    {
        // Réservé aux administrateurs
        $roleNorm = strtolower(trim((string) (Auth::user()?->role ?? '')));
        $isAdmin = in_array($roleNorm, ['administrateur', 'admin', 'super_admin'], true);
        if (!$isAdmin) {
            return redirect()->back()->with('message', 'Vous n\'êtes pas autorisé à annuler un panier.');
        }

        $panier = Panier::findOrFail($id);
        Log::debug('[DEBUG PANIER ANNULER] Avant', ['id' => $id, 'status' => $panier->status]);
        if ($panier->status === 'en_cours') {
            $panier->status = 'annulé';
            $panier->save();
            Log::debug('[DEBUG PANIER ANNULER] Après', ['id' => $id, 'status' => $panier->status]);
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
}
