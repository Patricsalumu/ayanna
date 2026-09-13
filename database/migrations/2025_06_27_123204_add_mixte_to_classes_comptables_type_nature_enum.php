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
        // Modifier l'enum pour ajouter 'mixte'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE classes_comptables MODIFY COLUMN type_nature ENUM('actif', 'passif', 'charge', 'produit', 'mixte')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirer 'mixte' de l'enum
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE classes_comptables MODIFY COLUMN type_nature ENUM('actif', 'passif', 'charge', 'produit')");
        }
    }
};
