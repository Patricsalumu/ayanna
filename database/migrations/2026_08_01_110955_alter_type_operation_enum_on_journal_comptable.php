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
        DB::statement("ALTER TABLE journal_comptable MODIFY COLUMN type_operation ENUM('vente', 'paiement', 'depense', 'recette', 'transfert', 'ajustement', 'achat', 'od', 'caisse') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE journal_comptable MODIFY COLUMN type_operation ENUM('vente', 'paiement', 'depense', 'recette', 'transfert', 'ajustement') NOT NULL");
    }
};
