<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_comptable', function (Blueprint $table) {
            $table->id();
            $table->date('date_ecriture');
            $table->string('numero_piece')->unique(); // Ex: VTE-20250626-001
            $table->string('libelle');
            $table->decimal('montant_total', 15, 2);
            
            // Références métier
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('point_de_vente_id')->nullable()->constrained('points_de_vente')->onDelete('set null');
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->onDelete('set null');
            $table->foreignId('panier_id')->nullable()->constrained('paniers')->onDelete('set null');
            
            // Traçabilité
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type_operation', ['vente', 'paiement', 'depense', 'recette', 'transfert', 'ajustement']);
            $table->enum('statut', ['brouillon', 'valide', 'cloture'])->default('valide');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['date_ecriture', 'entreprise_id']);
            $table->index(['point_de_vente_id', 'date_ecriture']);
            $table->index(['type_operation', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_comptable');
    }
};
