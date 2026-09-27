<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->unsignedInteger('dernier_numero_facture')->default(0);
        });

        Schema::table('paniers', function (Blueprint $table) {
            $table->unsignedInteger('numero_facture')->nullable()->index();
        });

        Schema::table('commandes', function (Blueprint $table) {
            $table->unsignedInteger('numero_facture')->nullable()->index();
        });

        $sequences = [];
        $paniers = DB::table('paniers')
            ->leftJoin('points_de_vente as pdv', 'paniers.point_de_vente_id', '=', 'pdv.id')
            ->leftJoin('table_restos', 'paniers.table_id', '=', 'table_restos.id')
            ->leftJoin('salles', 'table_restos.salle_id', '=', 'salles.id')
            ->leftJoin('users as opener', 'paniers.opened_by', '=', 'opener.id')
            ->select('paniers.id', DB::raw('COALESCE(pdv.entreprise_id, salles.entreprise_id, opener.entreprise_id) as entreprise_id'))
            ->orderBy('paniers.id')
            ->get();

        foreach ($paniers as $panier) {
            if (!$panier->entreprise_id) {
                continue;
            }

            $numeroFacture = ($sequences[$panier->entreprise_id] ?? 0) + 1;
            $sequences[$panier->entreprise_id] = $numeroFacture;

            DB::table('paniers')->where('id', $panier->id)->update(['numero_facture' => $numeroFacture]);
            DB::table('commandes')->where('panier_id', $panier->id)->update(['numero_facture' => $numeroFacture]);
        }

        foreach ($sequences as $entrepriseId => $numeroFacture) {
            DB::table('entreprises')->where('id', $entrepriseId)->update([
                'dernier_numero_facture' => $numeroFacture,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn('numero_facture');
        });

        Schema::table('paniers', function (Blueprint $table) {
            $table->dropColumn('numero_facture');
        });

        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropColumn('dernier_numero_facture');
        });
    }
};