<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('points_de_vente', function (Blueprint $table) {
            $table->boolean('interdire_commande_si_stock_null')->default(false)->after('comptabilite_active');
            $table->boolean('serveuse_peut_valider_paiement')->default(false)->after('interdire_commande_si_stock_null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('points_de_vente', function (Blueprint $table) {
            $table->dropColumn(['interdire_commande_si_stock_null', 'serveuse_peut_valider_paiement']);
        });
    }
};
