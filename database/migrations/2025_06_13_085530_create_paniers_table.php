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
        Schema::create('paniers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id'); // table du restaurant
            $table->unsignedBigInteger('point_de_vente_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('serveuse_id')->nullable();
            $table->unsignedBigInteger('opened_by')->nullable(); // utilisateur qui a ouvert le panier
            $table->unsignedBigInteger('last_modified_by')->nullable(); // utilisateur qui a modifié dernièrement
            $table->json('produits_json'); // [{produit_id, quantite}]
            $table->string('status')->default('en_cours'); // statut du panier (en_cours, annule, valide, ferme)
            $table->boolean('is_printed')->default(false); // si le panier a été imprimé
            $table->decimal('total', 10, 2)->default(0.00); // total du panier
            $table->decimal('total_ht', 10, 2)->default(0.00); // total hors taxes
            $table->decimal('total_tva', 10, 2)->default(0.00); // total TVA
            $table->decimal('total_ttc', 10, 2)->default(0.00); // total toutes taxes comprises
            $table->decimal('total_remise', 10, 2)->default(0.00); // total des remises
            $table->decimal('total_paye', 10, 2)->default(0.00); // total payé
            $table->decimal('total_reste', 10, 2)->default(0.00); // total restant à payer
            $table->decimal('pourboire', 10, 2)->default(0.00); // pourboire
            $table->string('mode_paiement')->nullable(); // mode de paiement (espèces, carte, etc.)
            $table->timestamps();
            $table->foreign('table_id')->references('id')->on('table_restos');
            $table->foreign('point_de_vente_id')->references('id')->on('point_de_ventes');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('serveuse_id')->references('id')->on('users');
            $table->timestamp('valide_at')->nullable(); // date et heure de validation du panier
            $table->timestamp('ferme_at')->nullable(); // date et heure de fermeture du panier
            $table->timestamp('annule_at')->nullable(); // date et heure d'annulation du panier
            $table->timestamp('opened_at')->nullable(); // date et heure d'ouverture du panier
            $table->timestamp('last_modified_at')->nullable(); // date et heure de la dernière modification du panier
            $table->timestamp('printed_at')->nullable();//date et heure d
            $table->foreign('opened_by')->references('id')->on('users');
            $table->foreign('last_modified_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paniers');
    }
};
