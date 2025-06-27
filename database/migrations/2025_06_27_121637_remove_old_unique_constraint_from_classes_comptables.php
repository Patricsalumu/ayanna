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
        Schema::table('classes_comptables', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte unique sur 'numero' seulement
            $table->dropUnique(['numero']); // Cela supprimera la contrainte classes_comptables_numero_unique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes_comptables', function (Blueprint $table) {
            // Remettre l'ancienne contrainte unique (pour rollback)
            $table->unique('numero', 'classes_comptables_numero_unique');
        });
    }
};
