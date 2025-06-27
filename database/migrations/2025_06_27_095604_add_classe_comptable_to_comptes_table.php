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
            $table->foreignId('classe_comptable_id')->nullable()->after('nom')->constrained('classes_comptables')->onDelete('restrict');
            $table->decimal('solde_debit', 15, 2)->default(0)->after('description'); // Solde débiteur
            $table->decimal('solde_credit', 15, 2)->default(0)->after('solde_debit'); // Solde créditeur
            $table->decimal('solde', 15, 2)->storedAs('solde_debit - solde_credit'); // Solde calculé automatiquement
            
            $table->index(['classe_comptable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropForeign(['classe_comptable_id']);
            $table->dropColumn(['classe_comptable_id', 'solde_debit', 'solde_credit', 'solde']);
        });
    }
};
