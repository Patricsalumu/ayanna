<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Panier;

class RecalculatePanierTotals extends Command
{
    protected $signature = "app:recalc-paniers {--apply : Appliquer les changements (par défaut dry-run)} {--sample=10 : Nombre d'exemples à afficher}";
    protected $description = 'Remplit panier_produit.prix manquants puis recalcule les totaux des paniers et commandes';

    public function handle()
    {
        $apply = $this->option('apply');
        $sample = (int) $this->option('sample');

        $this->info('1) Comptage des lignes pivot sans prix...');
        $missing = DB::table('panier_produit')->whereNull('prix')->count();
        $this->line("Lignes sans prix: $missing");

        if ($missing > 0) {
            $this->info('Préparation: assigner produit.prix_vente à pivot.prix pour les lignes NULL');
            if ($apply) {
                DB::beginTransaction();
                DB::statement('UPDATE panier_produit pp JOIN produits p ON p.id = pp.produit_id SET pp.prix = p.prix_vente WHERE pp.prix IS NULL');
                DB::commit();
                $this->info('Remplissage des prix pivot effectué.');
            } else {
                $this->info('Dry-run : pour appliquer utilisez --apply');
            }
        } else {
            $this->info('Aucune ligne pivot manquante.');
        }

        $this->info('2) Recalcul des totaux par panier (chunked)...');
        $sampleResults = [];

        $commandeAmountColumn = null;
        if (Schema::hasTable('commandes')) {
            $cols = Schema::getColumnListing('commandes');
            $candidates = ['montant','montant_total','total_ttc','total_ht','total'];
            foreach($candidates as $c) {
                if (in_array($c, $cols)) { $commandeAmountColumn = $c; break; }
            }
        }

        if (!$commandeAmountColumn) {
            $this->warn('Aucune colonne de montant identifiée sur la table `commandes`. Les commandes ne seront pas mises à jour.');
        } else {
            $this->info("La colonne de montant utilisée pour mise à jour des commandes sera: $commandeAmountColumn");
        }

        Panier::with('produits')->chunkById(200, function($paniers) use (&$sampleResults, $sample, $apply, $commandeAmountColumn) {
            foreach($paniers as $panier) {
                $totalHt = 0;
                $totalTva = 0;
                foreach($panier->produits as $p) {
                    $unit = $p->pivot->prix ?? $p->prix_vente ?? 0;
                    $qte = $p->pivot->quantite ?? 0;
                    $totalHt += $unit * $qte;
                    $taux = $p->taux_tva ?? 0;
                    $totalTva += ($unit * $qte) * ($taux/100);
                }
                $remise = $panier->remise ?? 0;
                $totalTtc = $totalHt - $remise + $totalTva;

                if (count($sampleResults) < $sample) {
                    $sampleResults[] = [
                        'panier_id' => $panier->id,
                        'old_total_ht' => $panier->total_ht ?? null,
                        'new_total_ht' => $totalHt,
                        'old_total_ttc' => $panier->total_ttc ?? null,
                        'new_total_ttc' => $totalTtc,
                    ];
                }

                if ($apply) {
                    $panier->total_ht = $totalHt;
                    $panier->total_tva = $totalTva;
                    $panier->total_ttc = $totalTtc;
                    $panier->saveQuietly();
                    if ($panier->commande && $commandeAmountColumn) {
                        $panier->commande->{$commandeAmountColumn} = $panier->total_ttc;
                        $panier->commande->saveQuietly();
                    }
                }
            }
        });

        $this->info('Exemples (premiers '.$sample.' paniers examinés) :');
        $this->table(['panier_id','old_total_ht','new_total_ht','old_total_ttc','new_total_ttc'], $sampleResults);

        $this->info('Terminé.');
        if (!$apply) $this->info('Rappel : exécutez avec --apply pour appliquer les changements.');
        return 0;
    }
}
