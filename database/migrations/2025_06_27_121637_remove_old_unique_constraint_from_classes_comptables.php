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
        // Cette migration a été créée pour supprimer une contrainte qui n'existe plus
        // (elle a été remplacée par unique(['numero', 'entreprise_id']))
        // Pas d'action nécessaire
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
