<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Commande;
use App\Models\Panier;
use App\Models\EntreeSortie;
use Illuminate\Support\Carbon;
use PDF;

class RapportController extends Controller
{
    // ...existing code...
    public function exportPdf(Request $request, $pointDeVenteId)
    {
        $date = $request->get('date', now()->toDateString());
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
            ->where('type', 'sortie')
            ->sum('montant');
        $solde = $recette - $totalCreance - $depenses;
        $pdf = PDF::loadView('rapport.pdf', compact('recette', 'totalCreance', 'detailsCreance', 'depenses', 'solde', 'date'));
        return $pdf->download('rapport_journalier_'.$date.'.pdf');
    }
}
