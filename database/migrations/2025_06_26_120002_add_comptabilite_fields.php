<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter des champs comptables aux points de vente
        Schema::table('points_de_vente', function (Blueprint $table) {
            $table->foreignId('compte_caisse_id')->nullable()->constrained('comptes')->onDelete('set null');
            $table->foreignId('compte_vente_id')->nullable()->constrained('comptes')->onDelete('set null');
            $table->foreignId('compte_client_id')->nullable()->constrained('comptes')->onDelete('set null');
            $table->boolean('comptabilite_active')->default(true);
        });
        
        // Ajouter des champs aux comptes
        Schema::table('comptes', function (Blueprint $table) {
            $table->string('classe_comptable', 1)->nullable(); // 1-7 selon plan comptable
            $table->string('sous_classe', 10)->nullable(); // Ex: 411, 701, etc.
            $table->boolean('est_collectif')->default(false); // Compte collectif (clients, fournisseurs)
            $table->decimal('solde_initial', 15, 2)->default(0);
            $table->date('date_solde_initial')->nullable();
        });
        
        // Ajouter le suivi comptable aux EntreeSortie
        Schema::table('entrees_sorties', function (Blueprint $table) {
            $table->foreignId('journal_id')->nullable()->constrained('journal_comptable')->onDelete('set null');
            $table->boolean('comptabilise')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('points_de_vente', function (Blueprint $table) {
            $table->dropForeign(['compte_caisse_id']);
            $table->dropForeign(['compte_vente_id']);
            $table->dropForeign(['compte_client_id']);
            $table->dropColumn(['compte_caisse_id', 'compte_vente_id', 'compte_client_id', 'comptabilite_active']);
        });
        
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn(['classe_comptable', 'sous_classe', 'est_collectif', 'solde_initial', 'date_solde_initial']);
        });
        
        Schema::table('entrees_sorties', function (Blueprint $table) {
            $table->dropForeign(['journal_id']);
            $table->dropColumn(['journal_id', 'comptabilise']);
        });
    }
};
