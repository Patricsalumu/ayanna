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
            // Vérifier si la colonne panier_id n'existe pas déjà avant de l'ajouter
            if (!Schema::hasColumn('paiements', 'panier_id')) {
                $table->unsignedBigInteger('panier_id')->nullable()->after('commande_id');
                $table->foreign('panier_id')->references('id')->on('paniers')->onDelete('set null');
            }

            // Ajouter les autres champs nécessaires pour le paiement des paniers
            if (!Schema::hasColumn('paiements', 'montant_recu')) {
                $table->decimal('montant_recu', 15, 2)->nullable()->after('montant');
            }

            if (!Schema::hasColumn('paiements', 'monnaie')) {
                $table->decimal('monnaie', 15, 2)->nullable()->after('montant_recu');
            }

            if (!Schema::hasColumn('paiements', 'mode_paiement')) {
                $table->string('mode_paiement')->nullable()->after('mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère avant de supprimer la colonne
            if (Schema::hasColumn('paiements', 'panier_id')) {
                $table->dropForeign(['panier_id']);
                $table->dropColumn('panier_id');
            }

            if (Schema::hasColumn('paiements', 'montant_recu')) {
                $table->dropColumn('montant_recu');
            }

            if (Schema::hasColumn('paiements', 'monnaie')) {
                $table->dropColumn('monnaie');
            }

            if (Schema::hasColumn('paiements', 'mode_paiement')) {
                $table->dropColumn('mode_paiement');
            }
        });
    }
};
