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
        // D'abord, on convertit les données existantes
        DB::statement("UPDATE entrees_sorties SET type = 'entree' WHERE type = 'credit'");
        DB::statement("UPDATE entrees_sorties SET type = 'sortie' WHERE type = 'debit'");
        
        // Ensuite, on modifie l'enum pour accepter les nouvelles valeurs
        DB::statement("ALTER TABLE entrees_sorties MODIFY COLUMN type ENUM('entree', 'sortie') NOT NULL DEFAULT 'entree'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre l'ancien enum
        DB::statement("ALTER TABLE entrees_sorties MODIFY COLUMN type ENUM('credit', 'debit') NOT NULL DEFAULT 'credit'");
        
        // Reconvertir les données
        DB::statement("UPDATE entrees_sorties SET type = 'credit' WHERE type = 'entree'");
        DB::statement("UPDATE entrees_sorties SET type = 'debit' WHERE type = 'sortie'");
    }
};
