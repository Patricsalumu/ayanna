<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compte;
use App\Models\PointDeVente;
use App\Services\ComptabiliteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransfertController extends Controller
{
    protected $comptabiliteService;

    public function __construct(ComptabiliteService $comptabiliteService)
    {
        $this->comptabiliteService = $comptabiliteService;
    }

    /**
     * Affiche la page de transfert inter-comptes
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? $user->entreprise->id;

        // Récupérer tous les comptes de l'entreprise avec leurs soldes
        $comptes = Compte::where('entreprise_id', $entrepriseId)
            ->with(['ecrituresDebit', 'ecrituresCredit'])
            ->orderBy('type')
            ->orderBy('nom')
            ->get()
            ->map(function($compte) {
                // Calculer le solde
                $debit = $compte->ecrituresDebit->sum('debit');
                $credit = $compte->ecrituresCredit->sum('credit');
                $compte->solde = $debit - $credit;
                return $compte;
            });

        // Récupérer les points de vente avec leurs comptes caisse configurés
        $pointsDeVente = PointDeVente::where('entreprise_id', $entrepriseId)
            ->with('compteCaisse')
            ->whereNotNull('compte_caisse_id')
            ->get();

        // Comptes spéciaux (banque, caisse générale)
        $compteBanque = $comptes->where('nom', 'LIKE', '%banque%')->first() 
            ?? $comptes->where('numero', '512')->first();
        
        $caisseGenerale = $comptes->where('nom', 'LIKE', '%caisse générale%')->first()
            ?? $comptes->where('nom', 'LIKE', '%caisse%')->where('numero', '531')->first();

        // Récupérer les transferts du jour (ou de la date filtrée)
        $dateFiltre = $request->get('date', now()->toDateString());
        $transferts = \App\Models\JournalComptable::where('entreprise_id', $entrepriseId)
            ->where('type_operation', 'transfert')
            ->where('date_ecriture', $dateFiltre)
            ->with(['ecritures.compte', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return view('transferts.index', compact(
            'comptes', 
            'pointsDeVente', 
            'transferts',
            'compteBanque',
            'caisseGenerale'
        ));
    }

    /**
     * Effectue un transfert entre deux comptes
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'compte_source_id' => 'required|exists:comptes,id',
                'compte_destination_id' => 'required|exists:comptes,id|different:compte_source_id',
                'montant' => 'required|numeric|min:1',
                'libelle' => 'required|string|max:500',
                'reference' => 'nullable|string|max:100',
                'type_operation' => 'required|string|in:vente,achat,od,caisse',
                'date_ecriture' => 'required|date',
                'heure_ecriture' => 'required|date_format:H:i'
            ]);

            $user = Auth::user();
            $entrepriseId = $user->entreprise_id ?? $user->entreprise->id;

            $compteSource = Compte::where('id', $request->compte_source_id)
                ->where('entreprise_id', $entrepriseId)
                ->first();
                
            $compteDestination = Compte::where('id', $request->compte_destination_id)
                ->where('entreprise_id', $entrepriseId)
                ->first();

            if (!$compteSource || !$compteDestination) {
                return back()->with('error', 'Comptes invalides ou non autorisés');
            }

            // L'écriture comptable est enregistrée sans contrôle de solde au compte crédité.
            $dateHeureEcriture = \Carbon\Carbon::parse($request->date_ecriture . ' ' . $request->heure_ecriture);

            $journal = $this->comptabiliteService->enregistrerTransfert(
                $compteSource,
                $compteDestination,
                $request->montant,
                $request->libelle,
                $entrepriseId,
                $user->id,
                $request->reference,
                $request->type_operation,
                $dateHeureEcriture
            );

            Log::info('Écriture comptable enregistrée', [
                'journal_id' => $journal->id,
                'type_operation' => $request->type_operation,
                'source' => $compteSource->nom,
                'destination' => $compteDestination->nom,
                'montant' => $request->montant,
                'user_id' => $user->id
            ]);

            return redirect()->route('comptabilite.journal', [
                'date_debut' => $dateHeureEcriture->toDateString(),
                'date_fin' => $dateHeureEcriture->toDateString(),
            ])->with('success', 
                "Écriture de {$request->montant} F enregistrée avec succès sur {$compteSource->nom} / {$compteDestination->nom}"
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors du transfert: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du transfert: ' . $e->getMessage());
        }
    }

    /**
     * API pour obtenir les transferts rapides disponibles pour un compte
     */
    public function transfertsRapides(Request $request, $compteId)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? $user->entreprise->id;

        $compte = Compte::where('id', $compteId)
            ->where('entreprise_id', $entrepriseId)
            ->first();

        if (!$compte) {
            return response()->json(['error' => 'Compte non trouvé'], 404);
        }

        $transfertsDisponibles = [];

        // Vers banque
        $compteBanque = Compte::where('entreprise_id', $entrepriseId)
            ->where('nom', 'LIKE', '%banque%')
            ->orWhere('numero', '512')
            ->first();

        if ($compteBanque && $compteBanque->id !== $compteId) {
            $transfertsDisponibles[] = [
                'id' => $compteBanque->id,
                'nom' => $compteBanque->nom,
                'type' => 'banque',
                'icon' => 'university',
                'libelle_suggest' => "Dépôt banque depuis {$compte->nom}"
            ];
        }

        // Vers caisse générale
        $caisseGenerale = Compte::where('entreprise_id', $entrepriseId)
            ->where('nom', 'LIKE', '%caisse générale%')
            ->orWhere('numero', '531')
            ->first();

        if ($caisseGenerale && $caisseGenerale->id !== $compteId) {
            $transfertsDisponibles[] = [
                'id' => $caisseGenerale->id,
                'nom' => $caisseGenerale->nom,
                'type' => 'caisse',
                'icon' => 'cash-register',
                'libelle_suggest' => "Transfert vers caisse générale depuis {$compte->nom}"
            ];
        }

        return response()->json($transfertsDisponibles);
    }

    /**
     * Calcule le solde d'un compte
     */
    private function calculerSoldeCompte(Compte $compte)
    {
        $debit = $compte->ecrituresDebit()->sum('debit');
        $credit = $compte->ecrituresCredit()->sum('credit');
        
        // Pour les comptes d'actif (caisse, banque), le solde = débit - crédit
        // Pour les comptes de passif (ventes), le solde = crédit - débit
        if ($compte->type === 'actif') {
            return $debit - $credit;
        } else {
            return $credit - $debit;
        }
    }

    /**
     * Historique des transferts avec pagination
     */
    public function historique(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? $user->entreprise->id;

        $query = \App\Models\JournalComptable::where('entreprise_id', $entrepriseId)
            ->where('type_operation', 'transfert')
            ->with(['ecritures.compte', 'user']);

        // Filtres
        if ($request->has('date_debut') && $request->date_debut) {
            $query->where('date_ecriture', '>=', $request->date_debut);
        }

        if ($request->has('date_fin') && $request->date_fin) {
            $query->where('date_ecriture', '<=', $request->date_fin);
        }

        if ($request->has('compte_id') && $request->compte_id) {
            $query->whereHas('ecritures', function($q) use ($request) {
                $q->where('compte_id', $request->compte_id);
            });
        }

        $transferts = $query->orderByDesc('created_at')->paginate(20);

        return view('transferts.historique', compact('transferts'));
    }
}
