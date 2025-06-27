<?php
namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\EntreeSortie;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CompteController extends Controller
{
    // Liste des comptes
    public function index(Request $request)
    {
        $entreprise_id = $request->get('entreprise_id') ?? (Auth::user()->entreprise_id ?? null);
        $comptes = Compte::where('entreprise_id', $entreprise_id)->orderBy('numero')->get();
        $entreprise = \App\Models\Entreprise::find($entreprise_id);
        return view('comptes.index', compact('comptes', 'entreprise'));
    }

    // Formulaire création
    public function create(Request $request)
    {
        $entreprise_id = $request->get('entreprise_id') ?? (Auth::user()->entreprise_id ?? null);
        $classesComptables = \App\Models\ClasseComptable::where('entreprise_id', $entreprise_id)->orderBy('numero')->get();
        return view('comptes.create', compact('entreprise_id', 'classesComptables'));
    }

    // Enregistrement d'un compte
    public function store(Request $request)
    {
        // Debug : log les données reçues
        Log::info('Données reçues pour création de compte', $request->all());
        $data = $request->validate([
            'numero' => 'required|unique:comptes',
            'nom' => 'required|string|max:255',
            'type' => 'required|in:actif,passif,charge,produit',
            'description' => 'nullable|string',
            'classe_comptable_id' => 'required|exists:classes_comptables,id',
            // 'entreprise_id' => 'required|exists:entreprises,id', // on retire la validation ici
        ]);
        $data['entreprise_id'] = Auth::user()->entreprise_id;
        $data['user_id'] = Auth::id();
        $compte = Compte::create($data);
        Log::info('Compte créé', ['compte' => $compte]);
        return redirect()->route('comptes.index')->with('success', 'Compte créé avec succès.');
    }

    // Formulaire édition
    public function edit(Compte $compte)
    {
        $classesComptables = \App\Models\ClasseComptable::where('entreprise_id', $compte->entreprise_id)->orderBy('numero')->get();
        return view('comptes.edit', compact('compte', 'classesComptables'));
    }

    // Mise à jour
    public function update(Request $request, Compte $compte)
    {
        // Debug : log les données reçues pour modification
        Log::info('Début modification compte', [
            'compte_id' => $compte->id,
            'compte_actuel' => $compte->toArray(),
            'donnees_recues' => $request->all()
        ]);

        try {
            $data = $request->validate([
                'numero' => 'required|unique:comptes,numero,' . $compte->id,
                'nom' => 'required|string|max:255',
                'type' => 'required|in:actif,passif,charge,produit',
                'description' => 'nullable|string',
                'classe_comptable_id' => 'required|exists:classes_comptables,id',
                // Suppression de la validation entreprise_id car le compte appartient déjà à une entreprise
                // 'entreprise_id' => 'required|exists:entreprises,id',
            ]);
            
            // Ne pas modifier l'entreprise_id lors de la mise à jour
            // Le compte reste attaché à son entreprise d'origine
            
            Log::info('Données validées pour modification compte', [
                'compte_id' => $compte->id,
                'donnees_validees' => $data
            ]);

            $compte->update($data);
            
            Log::info('Compte modifié avec succès', [
                'compte_id' => $compte->id,
                'compte_apres_modification' => $compte->fresh()->toArray()
            ]);

            return redirect()->route('comptes.index')->with('success', 'Compte modifié avec succès.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur de validation lors de la modification du compte', [
                'compte_id' => $compte->id,
                'erreurs_validation' => $e->errors(),
                'donnees_recues' => $request->all()
            ]);
            throw $e;
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la modification du compte', [
                'compte_id' => $compte->id,
                'message_erreur' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'donnees_recues' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'Une erreur est survenue lors de la modification du compte.']);
        }
    }

    // Suppression
    public function destroy(Compte $compte)
    {
        $compte->delete();
        return redirect()->route('comptes.index')->with('success', 'Compte supprimé.');
    }

    // Liste des entrées/sorties d'un compte
    public function mouvements(Compte $compte)
    {
        $mouvements = $compte->entreesSorties()->orderByDesc('created_at')->get();
        return view('comptes.mouvements', compact('compte', 'mouvements'));
    }

    // Ajout d'une entrée/sortie
    public function ajouterMouvement(Request $request, Compte $compte)
    {
        Log::info('Données reçues pour ajout mouvement', $request->all());
        $data = $request->validate([
            'montant' => 'required|numeric',
            'libele' => 'required|string',
            'type' => 'required|in:credit,debit',
        ]);
        Log::info('Données validées mouvement', $data);
        $data['user_id'] = Auth::id();
        $mouvement = $compte->entreesSorties()->create($data);
        Log::info('Mouvement créé', ['mouvement' => $mouvement]);
        return redirect()->route('comptes.mouvements', $compte)->with('success', 'Mouvement ajouté.');
    }

    // Suppression d'un mouvement
    public function supprimerMouvement(EntreeSortie $mouvement)
    {
        $compte = $mouvement->compte;
        $mouvement->delete();
        return redirect()->route('comptes.mouvements', $compte)->with('success', 'Mouvement supprimé.');
    }

    // Export PDF des mouvements filtrés
    public function exportMouvementsPdf(Request $request, Compte $compte)
    {
        $date = $request->get('date');
        $type = $request->get('type');
        $search = $request->get('search');
        $query = $compte->entreesSorties()->orderByDesc('created_at');
        if ($date) {
            $query->whereDate('created_at', $date);
        }
        if ($type) {
            $query->where('type', $type);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('libele', 'like', "%$search%")
                  ->orWhereRaw('DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") like ?', ["%$search%"]);
            });
        }
        $mouvements = $query->get();
        $totalCredit = $mouvements->where('type','credit')->sum('montant');
        $totalDebit = $mouvements->where('type','debit')->sum('montant');
        $pdf = Pdf::loadView('comptes.pdf_mouvements', compact('compte', 'mouvements', 'date', 'type', 'search', 'totalCredit', 'totalDebit'));
        return $pdf->download('mouvements_compte_'.$compte->numero.'.pdf');
    }
}
