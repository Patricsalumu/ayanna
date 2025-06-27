<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'enum pour ajouter 'charge' et 'produit'
        DB::statement("ALTER TABLE comptes MODIFY COLUMN type ENUM('actif', 'passif', 'charge', 'produit')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirer 'charge' et 'produit' de l'enum
        DB::statement("ALTER TABLE comptes MODIFY COLUMN type ENUM('actif', 'passif')");
    }
};
