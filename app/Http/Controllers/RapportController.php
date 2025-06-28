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
        
        // 1.A. Recettes VENTES : toutes les ventes du jour (tous modes de paiement)
        $commandes = Commande::whereDate('created_at', $date)
            ->whereHas('panier', function($q) use ($pointDeVenteId) {
                $q->where('point_de_vente_id', $pointDeVenteId);
            })
            ->get();
            
        $recettesVentes = $commandes->sum(function($cmd) {
            return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * $p->prix_vente; }) : 0);
        });
        
        // Détail ventes par mode de paiement
        $ventesParMode = $commandes->groupBy('mode_paiement')->map(function($cmds, $mode) {
            $total = $cmds->sum(function($cmd) {
                return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * $p->prix_vente; }) : 0);
            });
            return [
                'mode' => $mode ?: 'Non défini',
                'count' => $cmds->count(),
                'total' => $total
            ];
        });

        // 1.B. Recettes PAIEMENTS CRÉANCES : règlements de créances du jour
        $paiementsCreances = EntreeSortie::whereDate('created_at', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'entree')
            ->where('libele', 'LIKE', '%Règlement créance%')
            ->get();
            
        $recettesPaiementsCreances = $paiementsCreances->sum('montant');
        
        // 1.C. Recettes ENTRÉES DIVERSES : autres entrées du jour (boss, réservations, etc.)
        $entresDiverses = EntreeSortie::whereDate('created_at', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'entree')
            ->where('libele', 'NOT LIKE', '%Règlement créance%')
            ->get();
            
        $recettesEntreesDiverses = $entresDiverses->sum('montant');

        // 1.D. TOTAL RECETTES = Ventes + Paiements créances + Entrées diverses
        $totalRecettes = $recettesVentes + $recettesPaiementsCreances + $recettesEntreesDiverses;

        // 2. Créances EN COURS : commandes à crédit du jour (non encore payées)
        $creances = $commandes->whereIn('mode_paiement', ['compte_client', 'credit']);
        $totalCreance = $creances->sum(function($cmd) {
            return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * $p->prix_vente; }) : 0);
        });
        
        // Détail créances : clients + serveuses
        $detailsCreance = $creances->groupBy(function($cmd) {
            return $cmd->panier->client_id ?? 0;
        })->map(function($cmds, $clientId) {
            $client = $cmds->first()->panier->client->nom ?? 'Inconnu';
            $serveuses = $cmds->pluck('panier.serveuse.name')->unique()->toArray();
            $total = $cmds->sum(function($cmd) {
                return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * $p->prix_vente; }) : 0);
            });
            return [
                'client' => $client,
                'serveuses' => $serveuses,
                'total' => $total
            ];
        });

        // 3. Dépenses : total des sorties du jour
        $depenses = EntreeSortie::whereDate('created_at', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'sortie')
            ->sum('montant');

        // 4. Solde = Total recettes - Créances en cours - Dépenses
        $solde = $totalRecettes - $totalCreance - $depenses;

        return view('rapport.jour', compact(
            'totalRecettes', 'recettesVentes', 'recettesPaiementsCreances', 'recettesEntreesDiverses',
            'ventesParMode', 'paiementsCreances', 'entresDiverses',
            'totalCreance', 'detailsCreance', 'depenses', 'solde', 'date'
        ));
    }

    /**
     * Exporte le rapport du jour en PDF
     */
    public function exportPdf(Request $request, $pointDeVenteId)
    {
        $date = $request->get('date', now()->toDateString());
        $pointDeVente = \App\Models\PointDeVente::with('entreprise')->findOrFail($pointDeVenteId);
        $entreprise = $pointDeVente->entreprise;
        
        // 1.A. Recettes VENTES
        $commandes = Commande::whereDate('created_at', $date)
            ->whereHas('panier', function($q) use ($pointDeVenteId) {
                $q->where('point_de_vente_id', $pointDeVenteId);
            })
            ->get();
            
        $recettesVentes = $commandes->sum(function($cmd) {
            return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * $p->prix_vente; }) : 0);
        });
        
        $ventesParMode = $commandes->groupBy('mode_paiement')->map(function($cmds, $mode) {
            $total = $cmds->sum(function($cmd) {
                return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * $p->prix_vente; }) : 0);
            });
            return [
                'mode' => $mode ?: 'Non défini',
                'count' => $cmds->count(),
                'total' => $total
            ];
        });

        // 1.B. Recettes PAIEMENTS CRÉANCES
        $paiementsCreances = EntreeSortie::whereDate('created_at', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'entree')
            ->where('libele', 'LIKE', '%Règlement créance%')
            ->get();
            
        $recettesPaiementsCreances = $paiementsCreances->sum('montant');
        
        // 1.C. Recettes ENTRÉES DIVERSES
        $entresDiverses = EntreeSortie::whereDate('created_at', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'entree')
            ->where('libele', 'NOT LIKE', '%Règlement créance%')
            ->get();
            
        $recettesEntreesDiverses = $entresDiverses->sum('montant');
        $totalRecettes = $recettesVentes + $recettesPaiementsCreances + $recettesEntreesDiverses;
        
        // 2. Créances
        $creances = $commandes->whereIn('mode_paiement', ['compte_client', 'credit']);
        $totalCreance = $creances->sum(function($cmd) {
            return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * $p->prix_vente; }) : 0);
        });
        
        $detailsCreance = $creances->groupBy(function($cmd) {
            return $cmd->panier->client_id ?? 0;
        })->map(function($cmds, $clientId) {
            $client = $cmds->first()->panier->client->nom ?? 'Inconnu';
            $serveuses = $cmds->pluck('panier.serveuse.name')->unique()->toArray();
            $total = $cmds->sum(function($cmd) {
                return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * $p->prix_vente; }) : 0);
            });
            return [
                'client' => $client,
                'serveuses' => $serveuses,
                'total' => $total
            ];
        });
        
        // 3. Dépenses
        $depenses = EntreeSortie::whereDate('created_at', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->where('type', 'sortie')
            ->sum('montant');
            
        $solde = $totalRecettes - $totalCreance - $depenses;
        
        return Pdf::loadView('rapport.pdf', compact(
            'totalRecettes', 'recettesVentes', 'recettesPaiementsCreances', 'recettesEntreesDiverses',
            'ventesParMode', 'paiementsCreances', 'entresDiverses',
            'totalCreance', 'detailsCreance', 'depenses', 'solde', 'date', 'pointDeVente', 'entreprise'
        ))->download('rapport_journalier_'.$date.'.pdf');
    }
}
