<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('compte_vente_id')->nullable()->constrained('comptes')->nullOnDelete();
            $table->foreignId('compte_stock_id')->nullable()->constrained('comptes')->nullOnDelete();
            $table->foreignId('compte_variation_stock_id')->nullable()->constrained('comptes')->nullOnDelete();
        });

        Schema::table('modes_paiement', function (Blueprint $table) {
            $table->foreignId('compte_id')->nullable()->constrained('comptes')->nullOnDelete();
        });

        Schema::table('journal_comptable', function (Blueprint $table) {
            $table->string('session', 20)->nullable()->after('point_de_vente_id');
            $table->unique(
                ['point_de_vente_id', 'date_ecriture', 'session', 'type_operation'],
                'journal_session_operation_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('journal_comptable', function (Blueprint $table) {
            $table->dropUnique('journal_session_operation_unique');
            $table->dropColumn('session');
        });

        Schema::table('modes_paiement', function (Blueprint $table) {
            $table->dropConstrainedForeignId('compte_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('compte_vente_id');
            $table->dropConstrainedForeignId('compte_stock_id');
            $table->dropConstrainedForeignId('compte_variation_stock_id');
        });
    }
};