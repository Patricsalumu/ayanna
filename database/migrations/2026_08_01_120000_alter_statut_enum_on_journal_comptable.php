<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE journal_comptable MODIFY COLUMN statut ENUM('brouillon', 'valide', 'cloture', 'annule') NOT NULL DEFAULT 'brouillon'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE journal_comptable MODIFY COLUMN statut ENUM('brouillon', 'valide', 'cloture') NOT NULL DEFAULT 'valide'");
    }
};
