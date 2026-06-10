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
        Schema::create('commandes_produits', function (Blueprint $table) {
            $table->unsignedBigInteger('commande_id')->nullable();
            $table->unsignedBigInteger('produit_id')->nullable();
            $table->unsignedBigInteger('utilisateur_id')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->date('date_commande'); // Date de la commande
            $table->boolean('statut'); // Heure de la commande
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes_produits');
    }
};
