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
        Schema::table('ventes', function (Blueprint $table) {
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');
            $table->integer('quantite');
            $table->decimal('prix_unitaire_vente', 10, 2);
            $table->decimal('montant_total', 10, 2);
            $table->dateTime('date_vente')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropForeign(['produit_id']);
            $table->dropColumn([
                'produit_id',
                'quantite',
                'prix_unitaire_vente',
                'montant_total',
                'date_vente',
            ]);
        });
    }
};
