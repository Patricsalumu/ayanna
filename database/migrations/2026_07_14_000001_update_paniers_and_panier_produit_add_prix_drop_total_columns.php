<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('panier_produit', function (Blueprint $table) {
            if (!Schema::hasColumn('panier_produit', 'prix')) {
                $table->decimal('prix', 10, 2)->nullable()->after('quantite');
            }
        });

        // Populate existing panier_produit rows with current product price when possible.
        DB::table('panier_produit')
            ->leftJoin('produits', 'panier_produit.produit_id', '=', 'produits.id')
            ->whereNull('panier_produit.prix')
            ->update(['panier_produit.prix' => DB::raw('produits.prix_vente')]);

        Schema::table('paniers', function (Blueprint $table) {
            if (Schema::hasColumn('paniers', 'total')) {
                $table->dropColumn('total');
            }
            if (Schema::hasColumn('paniers', 'total_paye')) {
                $table->dropColumn('total_paye');
            }
            if (Schema::hasColumn('paniers', 'pourboire')) {
                $table->dropColumn('pourboire');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panier_produit', function (Blueprint $table) {
            if (Schema::hasColumn('panier_produit', 'prix')) {
                $table->dropColumn('prix');
            }
        });

        Schema::table('paniers', function (Blueprint $table) {
            if (!Schema::hasColumn('paniers', 'total')) {
                $table->decimal('total', 10, 2)->default(0.00)->after('status');
            }
            if (!Schema::hasColumn('paniers', 'total_paye')) {
                $table->decimal('total_paye', 10, 2)->default(0.00)->after('total_remise');
            }
            if (!Schema::hasColumn('paniers', 'pourboire')) {
                $table->decimal('pourboire', 10, 2)->default(0.00)->after('total_paye');
            }
        });
    }
};
