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
        // points_de_vente -> modules & entreprises
        Schema::table('points_de_vente', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
        });

        // categorie_point_de_vente -> points_de_vente & categories
        Schema::table('categorie_point_de_vente', function (Blueprint $table) {
            $table->foreign('point_de_vente_id')->references('id')->on('points_de_vente')->onDelete('cascade');
            $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('cascade');
        });

        // salle_point_de_vente -> points_de_vente & salles
        Schema::table('salle_point_de_vente', function (Blueprint $table) {
            $table->foreign('point_de_vente_id')->references('id')->on('points_de_vente')->onDelete('cascade');
            $table->foreign('salle_id')->references('id')->on('salles')->onDelete('cascade');
        });

        // commandes_produits -> commandes, produits (utilisateurs & tables n'existent pas)
        Schema::table('commandes_produits', function (Blueprint $table) {
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('cascade');
            $table->foreign('produit_id')->references('id')->on('produits')->onDelete('cascade');
        });

        // commandes -> clients, points_de_vente, paniers (utilisateurs & tables n'existent pas)
        Schema::table('commandes', function (Blueprint $table) {
            if (Schema::hasColumn('commandes', 'client_id')) {
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            }
            if (Schema::hasColumn('commandes', 'point_de_vente_id')) {
                $table->foreign('point_de_vente_id')->references('id')->on('points_de_vente')->onDelete('set null');
            }
            if (Schema::hasColumn('commandes', 'panier_id')) {
                $table->foreign('panier_id')->references('id')->on('paniers')->onDelete('cascade');
            }
        });

        // paiements -> comptes, commandes, users
        Schema::table('paiements', function (Blueprint $table) {
            $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // entreprise_module -> entreprises, modules
        Schema::table('entreprise_module', function (Blueprint $table) {
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });

        // paniers -> table_restos, points_de_vente, clients, users
        Schema::table('paniers', function (Blueprint $table) {
            $table->foreign('table_id')->references('id')->on('table_restos')->onDelete('cascade');
            $table->foreign('point_de_vente_id')->references('id')->on('points_de_vente')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->foreign('serveuse_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('opened_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('last_modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paniers', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropForeign(['point_de_vente_id']);
            $table->dropForeign(['client_id']);
            $table->dropForeign(['serveuse_id']);
            $table->dropForeign(['opened_by']);
            $table->dropForeign(['last_modified_by']);
        });

        Schema::table('entreprise_module', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->dropForeign(['module_id']);
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['compte_id']);
            $table->dropForeign(['commande_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('commandes', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['point_de_vente_id']);
            $table->dropForeign(['panier_id']);
        });

        Schema::table('commandes_produits', function (Blueprint $table) {
            $table->dropForeign(['commande_id']);
            $table->dropForeign(['produit_id']);
        });

        Schema::table('salle_point_de_vente', function (Blueprint $table) {
            $table->dropForeign(['point_de_vente_id']);
            $table->dropForeign(['salle_id']);
        });

        Schema::table('categorie_point_de_vente', function (Blueprint $table) {
            $table->dropForeign(['point_de_vente_id']);
            $table->dropForeign(['categorie_id']);
        });

        Schema::table('points_de_vente', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropForeign(['entreprise_id']);
        });
    }
};
