<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id')->nullable();
            $table->unsignedBigInteger('point_de_vente_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->date('date_commande'); // Date de la commande   
            $table->unsignedBigInteger('panier_id')->nullable();
            $table->string('mode_paiement')->nullable();
            $table->string('statut')->default('en attente'); // Statut de la commande (en attente, en cours, terminée, annulée)
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
