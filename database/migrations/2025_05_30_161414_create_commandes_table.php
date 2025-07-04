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
            $table->foreignId('utilisateur_id')->constrained()->onDelete('cascade');
             $table->foreignId('point_de_vente_id')->nullable()->constrained('points_de_vente')->onDelete('set null');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained('tables')->onDelete('cascade');
            $table->date('date_commande'); // Date de la commande   
            $table->unsignedBigInteger('panier_id');
            $table->string('mode_paiement')->nullable();
            $table->string('statut')->default('en attente'); // Statut de la commande (en attente, en cours, terminée, annulée)
            $table->foreign('panier_id')->references('id')->on('paniers');
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
