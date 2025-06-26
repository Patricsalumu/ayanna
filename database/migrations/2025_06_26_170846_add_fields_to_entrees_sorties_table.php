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
        Schema::table('entrees_sorties', function (Blueprint $table) {
            // Vérifier si les colonnes n'existent pas avant de les ajouter
            if (!Schema::hasColumn('entrees_sorties', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('entrees_sorties', 'point_de_vente_id')) {
                $table->foreignId('point_de_vente_id')->nullable()->constrained('point_de_ventes')->onDelete('set null');
            }
            if (!Schema::hasColumn('entrees_sorties', 'journal_id')) {
                $table->foreignId('journal_id')->nullable()->constrained('journal_comptable')->onDelete('set null');
            }
            if (!Schema::hasColumn('entrees_sorties', 'comptabilise')) {
                $table->boolean('comptabilise')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entrees_sorties', function (Blueprint $table) {
            if (Schema::hasColumn('entrees_sorties', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('entrees_sorties', 'point_de_vente_id')) {
                $table->dropForeign(['point_de_vente_id']);
                $table->dropColumn('point_de_vente_id');
            }
            if (Schema::hasColumn('entrees_sorties', 'journal_id')) {
                $table->dropForeign(['journal_id']);
                $table->dropColumn('journal_id');
            }
            if (Schema::hasColumn('entrees_sorties', 'comptabilise')) {
                $table->dropColumn('comptabilise');
            }
        });
    }
};
