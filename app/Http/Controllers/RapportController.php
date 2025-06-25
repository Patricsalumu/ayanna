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
        // 1. Recette journalière : total de toutes les ventes du jour
        $commandes = Commande::whereDate('created_at', $date)
            ->whereHas('panier', function($q) use ($pointDeVenteId) {
                $q->where('point_de_vente_id', $pointDeVenteId);
            })
            ->get();
        $recette = $commandes->sum(function($cmd) {
            return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * $p->prix_vente; }) : 0);
        });

        // 2. Créances : commandes à crédit du jour
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

        // 3. Dépenses : total des montants passifs (dépenses) du jour
        $depenses = EntreeSortie::whereDate('created_at', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->whereHas('compte', function($q) {
                $q->where('type', 'passif');
            })
            ->sum('montant');

        // 4. Solde
        $solde = $recette - $totalCreance - $depenses;

        return view('rapport.jour', compact('recette', 'totalCreance', 'detailsCreance', 'depenses', 'solde', 'date'));
    }

    /**
     * Exporte le rapport du jour en PDF
     */
    public function exportPdf(Request $request, $pointDeVenteId)
    {
        $date = $request->get('date', now()->toDateString());
        $pointDeVente = \App\Models\PointDeVente::with('entreprise')->findOrFail($pointDeVenteId);
        $entreprise = $pointDeVente->entreprise;
        $commandes = Commande::whereDate('created_at', $date)
            ->whereHas('panier', function($q) use ($pointDeVenteId) {
                $q->where('point_de_vente_id', $pointDeVenteId);
            })
            ->get();
        $recette = $commandes->sum(function($cmd) {
            return $cmd->montant ?? ($cmd->panier ? $cmd->panier->produits->sum(function($p) { return $p->pivot->quantite * $p->prix_vente; }) : 0);
        });
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
        $depenses = EntreeSortie::whereDate('created_at', $date)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->whereHas('compte', function($q) {
                $q->where('type', 'passif');
            })
            ->sum('montant');
        $solde = $recette - $totalCreance - $depenses;
        return Pdf::loadView('rapport.pdf', compact('recette', 'totalCreance', 'detailsCreance', 'depenses', 'solde', 'date', 'pointDeVente', 'entreprise'))
            ->download('rapport_journalier_'.$date.'.pdf');
    }
}
