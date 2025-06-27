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
        Schema::table('comptes', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte unique sur numero
            $table->dropUnique('comptes_numero_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            // Remettre la contrainte unique sur numero (si jamais on veut rollback)
            $table->unique('numero', 'comptes_numero_unique');
        });
    }
};
