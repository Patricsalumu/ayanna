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
        Schema::table('entree_sorties', function (Blueprint $table) {
            // Modifier l'enum pour utiliser 'entree' et 'sortie' au lieu de 'credit' et 'debit'
            $table->enum('type', ['entree', 'sortie'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entree_sorties', function (Blueprint $table) {
            // Remettre l'ancien enum
            $table->enum('type', ['credit', 'debit'])->change();
        });
    }
};
