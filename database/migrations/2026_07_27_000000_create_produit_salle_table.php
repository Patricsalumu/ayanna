<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produit_salle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produit_id');
            $table->unsignedBigInteger('salle_id');
            $table->decimal('prix', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['produit_id', 'salle_id']);
            $table->foreign('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->foreign('salle_id')->references('id')->on('salles')->onDelete('cascade');
        });

        if (Schema::hasColumn('produits', 'prix_vente')) {
            $rows = DB::table('produits as p')
                ->join('categories as c', 'p.categorie_id', '=', 'c.id')
                ->join('salles as s', 's.entreprise_id', '=', 'c.entreprise_id')
                ->select('p.id as produit_id', 's.id as salle_id', 'p.prix_vente as prix')
                ->get();

            $insertData = [];
            foreach ($rows as $row) {
                $insertData[] = [
                    'produit_id' => $row->produit_id,
                    'salle_id' => $row->salle_id,
                    'prix' => $row->prix,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($insertData)) {
                DB::table('produit_salle')->insert($insertData);
            }

            Schema::table('produits', function (Blueprint $table) {
                if (Schema::hasColumn('produits', 'prix_vente')) {
                    $table->dropColumn('prix_vente');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('produits', 'prix_vente') === false) {
            Schema::table('produits', function (Blueprint $table) {
                $table->decimal('prix_vente', 10, 2)->default(0)->after('prix_achat');
            });

            DB::table('produits as p')
                ->leftJoin('produit_salle as ps', function ($join) {
                    $join->on('p.id', '=', 'ps.produit_id');
                })
                ->whereColumn('p.id', 'ps.produit_id')
                ->update(['p.prix_vente' => DB::raw('ps.prix')]);
        }

        Schema::dropIfExists('produit_salle');
    }
};
