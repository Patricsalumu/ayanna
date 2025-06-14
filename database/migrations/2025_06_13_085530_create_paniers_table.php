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
        Schema::create('paniers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id'); // table du restaurant
            $table->unsignedBigInteger('point_de_vente_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('serveuse_id')->nullable();
            $table->unsignedBigInteger('opened_by')->nullable(); // utilisateur qui a ouvert le panier
            $table->unsignedBigInteger('last_modified_by')->nullable(); // utilisateur qui a modifié dernièrement
            $table->json('produits_json'); // [{produit_id, quantite}]
            $table->timestamps();
            $table->foreign('table_id')->references('id')->on('table_restos');
            $table->foreign('point_de_vente_id')->references('id')->on('point_de_ventes');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('serveuse_id')->references('id')->on('users');
            $table->timestamp('valide_at')->nullable()->after('last_modified_by');
            $table->foreign('opened_by')->references('id')->on('users');
            $table->foreign('last_modified_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paniers');
    }
};
