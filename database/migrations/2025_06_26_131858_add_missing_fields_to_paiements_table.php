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
        Schema::table('paiements', function (Blueprint $table) {
            // Vérifier et ajouter les colonnes manquantes
            if (!Schema::hasColumn('paiements', 'montant_restant')) {
                $table->decimal('montant_restant', 15, 2)->default(0)->after('montant');
            }
            if (!Schema::hasColumn('paiements', 'notes')) {
                $table->text('notes')->nullable()->after('mode');
            }
            if (!Schema::hasColumn('paiements', 'est_solde')) {
                $table->boolean('est_solde')->default(false)->after('notes');
            }
            if (!Schema::hasColumn('paiements', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('est_solde');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('paiements', 'statut')) {
                $table->string('statut')->default('validé')->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            if (Schema::hasColumn('paiements', 'statut')) {
                $table->dropColumn('statut');
            }
            if (Schema::hasColumn('paiements', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('paiements', 'est_solde')) {
                $table->dropColumn('est_solde');
            }
            if (Schema::hasColumn('paiements', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('paiements', 'montant_restant')) {
                $table->dropColumn('montant_restant');
            }
        });
    }
};
