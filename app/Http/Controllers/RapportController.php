<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Commande;
use App\Models\Panier;
use App\Models\EntreeSortie;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RapportController extends Controller
{
    /**
     * Affiche le rapport du jour pour le point de vente (recette, créances, dépenses, solde)
     */
    public function rapportJour(Request $request, $pointDeVenteId)
    {
        $date = $request->get('date', now()->toDateString());
        $selectedSessionFrom = $request->get('session_from', null);
        $selectedSessionTo = $request->get('session_to', null);

        // Récupérer les sessions disponibles pour ce point de vente
        $sessionStocks = \App\Models\StockJournalier::where('point_de_vente_id', $pointDeVenteId)
            ->orderByDesc('session')
            ->get();
        $sessions = $sessionStocks->groupBy('session')->map(function($stocks, $session) {
            $first = $stocks->sortBy('validated_at')->first();
            return (object)[
                'session' => $session,
                'validated_at' => $first->validated_at ?? $first->created_at,
                'point_de_vente_id' => $first->point_de_vente_id,
            ];
        })->values();
        
        // Déterminer bornes temporelles selon sessions selectionnées ou date
        $start = null;
        $end = null;
        if ($selectedSessionFrom || $selectedSessionTo) {
            $fromInfo = $sessions->firstWhere('session', $selectedSessionFrom);
            $toInfo = $sessions->firstWhere('session', $selectedSessionTo);
            $start = $fromInfo->validated_at ?? null;
            $end = $toInfo->validated_at ?? null;
            if ($start && $end && $start > $end) {
                [$start, $end] = [$end, $start];
            }
            // si toInfo a closed_at enregistré, l'utiliser comme fin
            if ($toInfo) {
                $closedAtTo = \App\Models\Historiquepdv::where('point_de_vente_id', $toInfo->point_de_vente_id)
                    ->where('etat', 'ferme')
                    ->where('opened_at', $toInfo->validated_at)
                    ->value('closed_at');
                if ($closedAtTo) $end = $closedAtTo;
                else if ($end) $end = Carbon::parse($end)->endOfDay();
                else if ($end) $end = Carbon::parse($end)->endOfDay();
            }
        }

        // 1.A. Recettes VENTES : ventes dans l'intervalle ou sur la date
        $commandesQuery = Commande::whereHas('panier', function($q) use ($pointDeVenteId) {
                $q->where('point_de_vente_id', $pointDeVenteId);
            });
        if ($start && $end) {
            $commandesQuery = $commandesQuery->whereBetween('created_at', [$start, $end]);
        } else {
            $commandesQuery = $commandesQuery->whereDate('created_at', $date);
        }
        $commandes = $commandesQuery->get();
            
        $recettesVentes = $commandes->sum(function($cmd) {
            return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * (($p->pivot->prix ?? $p->prix_vente) ?? 0); }) : 0);
        });
        
        // Détail ventes par mode de paiement
        $ventesParMode = $commandes->groupBy('mode_paiement')->map(function($cmds, $mode) {
            $total = $cmds->sum(function($cmd) {
                return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * (($p->pivot->prix ?? $p->prix_vente) ?? 0); }) : 0);
            });
            return [
                'mode' => $mode ?: 'Non défini',
                'count' => $cmds->count(),
                'total' => $total
            ];
        });

        // 1.B. Recettes PAIEMENTS CRÉANCES : règlements de créances du jour
        $paiementsCreancesQuery = EntreeSortie::where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'entree')
            ->where('libele', 'LIKE', '%Règlement créance%');
        if ($start && $end) {
            $paiementsCreancesQuery = $paiementsCreancesQuery->whereBetween('created_at', [$start, $end]);
        } else {
            $paiementsCreancesQuery = $paiementsCreancesQuery->whereDate('created_at', $date);
        }
        $paiementsCreances = $paiementsCreancesQuery->get();
            
        $recettesPaiementsCreances = $paiementsCreances->sum('montant');
        
        // 1.C. Recettes ENTRÉES DIVERSES : autres entrées du jour (boss, réservations, etc.)
        $entresDiversesQuery = EntreeSortie::where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'entree')
            ->where('libele', 'NOT LIKE', '%Règlement créance%');
        if ($start && $end) {
            $entresDiversesQuery = $entresDiversesQuery->whereBetween('created_at', [$start, $end]);
        } else {
            $entresDiversesQuery = $entresDiversesQuery->whereDate('created_at', $date);
        }
        $entresDiverses = $entresDiversesQuery->get();
            
        $recettesEntreesDiverses = $entresDiverses->sum('montant');

        // 1.D. TOTAL RECETTES = Ventes + Paiements créances + Entrées diverses
        $totalRecettes = $recettesVentes + $recettesPaiementsCreances + $recettesEntreesDiverses;

        // 2. Créances EN COURS : commandes à crédit du jour (non encore payées)
        $creances = $commandes->whereIn('mode_paiement', ['compte_client', 'credit']);
        $totalCreance = $creances->sum(function($cmd) {
            return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * (($p->pivot->prix ?? $p->prix_vente) ?? 0); }) : 0);
        });
        
        // Détail créances : clients + serveuses
        $detailsCreance = $creances->groupBy(function($cmd) {
            return $cmd->panier->client_id ?? 0;
        })->map(function($cmds, $clientId) {
            $client = $cmds->first()->panier->client->nom ?? 'Inconnu';
            $serveuses = $cmds->pluck('panier.serveuse.name')->unique()->toArray();
            $total = $cmds->sum(function($cmd) {
                return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * (($p->pivot->prix ?? $p->prix_vente) ?? 0); }) : 0);
            });
            return [
                'client' => $client,
                'serveuses' => $serveuses,
                'total' => $total
            ];
        });

        // 3. Dépenses : total des sorties du jour
        $depensesQuery = EntreeSortie::where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'sortie');
        if ($start && $end) {
            $depensesQuery = $depensesQuery->whereBetween('created_at', [$start, $end]);
        } else {
            $depensesQuery = $depensesQuery->whereDate('created_at', $date);
        }
        $depenses = $depensesQuery->sum('montant');

        // 4. Solde = Total recettes - Créances en cours - Dépenses
        $solde = $totalRecettes - $totalCreance - $depenses;

        return view('rapport.jour', compact(
            'totalRecettes', 'recettesVentes', 'recettesPaiementsCreances', 'recettesEntreesDiverses',
            'ventesParMode', 'paiementsCreances', 'entresDiverses',
            'totalCreance', 'detailsCreance', 'depenses', 'solde', 'date', 'sessions', 'selectedSessionFrom', 'selectedSessionTo'
        ));
    }

    /**
     * Exporte le rapport du jour en PDF
     */
    public function exportPdf(Request $request, $pointDeVenteId)
    {
        $date = $request->get('date', now()->toDateString());
        $selectedSessionFrom = $request->get('session_from', null);
        $selectedSessionTo = $request->get('session_to', null);
        $start = null; $end = null;
        if ($selectedSessionFrom || $selectedSessionTo) {
            $sessionStocks = \App\Models\StockJournalier::where('point_de_vente_id', $pointDeVenteId)->get();
            $sessions = $sessionStocks->groupBy('session')->map(function($stocks, $session) {
                $first = $stocks->sortBy('validated_at')->first();
                return (object)['session'=>$session,'validated_at'=>$first->validated_at ?? $first->created_at,'point_de_vente_id'=>$first->point_de_vente_id];
            })->values();
            $fromInfo = $sessions->firstWhere('session', $selectedSessionFrom);
            $toInfo = $sessions->firstWhere('session', $selectedSessionTo);
            $start = $fromInfo->validated_at ?? null;
            $end = $toInfo->validated_at ?? null;
            if ($start && $end && $start > $end) { [$start, $end] = [$end, $start]; }
            if ($toInfo) {
                $closedAtTo = \App\Models\Historiquepdv::where('point_de_vente_id', $toInfo->point_de_vente_id)
                    ->where('etat', 'ferme')
                    ->where('opened_at', $toInfo->validated_at)
                    ->value('closed_at');
                if ($closedAtTo) $end = $closedAtTo;
            }
        }
        $pointDeVente = \App\Models\PointDeVente::with('entreprise')->findOrFail($pointDeVenteId);
        $entreprise = $pointDeVente->entreprise;
        
        // 1.A. Recettes VENTES
        $commandesQuery = Commande::whereHas('panier', function($q) use ($pointDeVenteId) {
                $q->where('point_de_vente_id', $pointDeVenteId);
            });
        if ($start && $end) $commandesQuery = $commandesQuery->whereBetween('created_at', [$start, $end]);
        else $commandesQuery = $commandesQuery->whereDate('created_at', $date);
        $commandes = $commandesQuery->get();
            
        $recettesVentes = $commandes->sum(function($cmd) {
            return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * (($p->pivot->prix ?? $p->prix_vente) ?? 0); }) : 0);
        });
        
        $ventesParMode = $commandes->groupBy('mode_paiement')->map(function($cmds, $mode) {
            $total = $cmds->sum(function($cmd) {
                return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * (($p->pivot->prix ?? $p->prix_vente) ?? 0); }) : 0);
            });
            return [
                'mode' => $mode ?: 'Non défini',
                'count' => $cmds->count(),
                'total' => $total
            ];
        });

        // 1.B. Recettes PAIEMENTS CRÉANCES
        $paiementsCreancesQuery = EntreeSortie::where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'entree')
            ->where('libele', 'LIKE', '%Règlement créance%');
        if ($start && $end) $paiementsCreancesQuery = $paiementsCreancesQuery->whereBetween('created_at', [$start, $end]);
        else $paiementsCreancesQuery = $paiementsCreancesQuery->whereDate('created_at', $date);
        $paiementsCreances = $paiementsCreancesQuery->get();
            
        $recettesPaiementsCreances = $paiementsCreances->sum('montant');
        
        // 1.C. Recettes ENTRÉES DIVERSES
        $entresDiversesQuery = EntreeSortie::where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'entree')
            ->where('libele', 'NOT LIKE', '%Règlement créance%');
        if ($start && $end) $entresDiversesQuery = $entresDiversesQuery->whereBetween('created_at', [$start, $end]);
        else $entresDiversesQuery = $entresDiversesQuery->whereDate('created_at', $date);
        $entresDiverses = $entresDiversesQuery->get();
            
        $recettesEntreesDiverses = $entresDiverses->sum('montant');
        $totalRecettes = $recettesVentes + $recettesPaiementsCreances + $recettesEntreesDiverses;
        
        // 2. Créances
        $creances = $commandes->whereIn('mode_paiement', ['compte_client', 'credit']);
        $totalCreance = $creances->sum(function($cmd) {
            return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * (($p->pivot->prix ?? $p->prix_vente) ?? 0); }) : 0);
        });
        
        $detailsCreance = $creances->groupBy(function($cmd) {
            return $cmd->panier->client_id ?? 0;
        })->map(function($cmds, $clientId) {
            $client = $cmds->first()->panier->client->nom ?? 'Inconnu';
            $serveuses = $cmds->pluck('panier.serveuse.name')->unique()->toArray();
            $total = $cmds->sum(function($cmd) {
                return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * (($p->pivot->prix ?? $p->prix_vente) ?? 0); }) : 0);
            });
            return [
                'client' => $client,
                'serveuses' => $serveuses,
                'total' => $total
            ];
        });
        
        // 3. Dépenses
        $depensesQuery = EntreeSortie::where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'sortie');
        if ($start && $end) $depensesQuery = $depensesQuery->whereBetween('created_at', [$start, $end]);
        else $depensesQuery = $depensesQuery->whereDate('created_at', $date);
        $depenses = $depensesQuery->sum('montant');
            
        $solde = $totalRecettes - $totalCreance - $depenses;
        
        return Pdf::loadView('rapport.pdf', compact(
            'totalRecettes', 'recettesVentes', 'recettesPaiementsCreances', 'recettesEntreesDiverses',
            'ventesParMode', 'paiementsCreances', 'entresDiverses',
            'totalCreance', 'detailsCreance', 'depenses', 'solde', 'date', 'pointDeVente', 'entreprise'
        ))->download('rapport_journalier_'.$date.'.pdf');
    }
}
