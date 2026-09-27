<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('paniers')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('commandes')
                    ->whereColumn('commandes.panier_id', 'paniers.id');
            })
            ->update(['numero_facture' => null]);

        foreach (DB::table('entreprises')->orderBy('id')->get(['id']) as $entreprise) {
            $dernierNumero = DB::table('commandes')
                ->join('paniers', 'commandes.panier_id', '=', 'paniers.id')
                ->leftJoin('points_de_vente as pdv', 'paniers.point_de_vente_id', '=', 'pdv.id')
                ->leftJoin('table_restos', 'paniers.table_id', '=', 'table_restos.id')
                ->leftJoin('salles', 'table_restos.salle_id', '=', 'salles.id')
                ->leftJoin('users as opener', 'paniers.opened_by', '=', 'opener.id')
                ->whereRaw('COALESCE(pdv.entreprise_id, salles.entreprise_id, opener.entreprise_id) = ?', [$entreprise->id])
                ->max('commandes.numero_facture');

            DB::table('entreprises')->where('id', $entreprise->id)->update([
                'dernier_numero_facture' => (int) ($dernierNumero ?? 0),
            ]);
        }
    }

    public function down(): void
    {
    }
};
