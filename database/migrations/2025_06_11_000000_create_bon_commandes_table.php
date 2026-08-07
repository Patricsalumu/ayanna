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
        Schema::create('bon_commandes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('numero_bon');
            $table->unsignedBigInteger('panier_id');
            $table->unsignedBigInteger('serveuse_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('utilisateur_id');
            $table->json('produits_json');
            $table->timestamps();

            // Relations ajoutées plus tard, une fois que les tables référencées existent

            // Index pour optimiser les recherches
            $table->index(['panier_id', 'created_at']);
            $table->index(['serveuse_id', 'created_at']);
            $table->index(['numero_bon', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_commandes');
    }
};
