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

            // Index et relations
            $table->foreign('panier_id')->references('id')->on('paniers')->onDelete('cascade');
            $table->foreign('serveuse_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->foreign('utilisateur_id')->references('id')->on('users')->onDelete('restrict');

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
