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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compte_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('commande_id')->constrained()->onDelete('cascade');
            $table->decimal('montant', 10, 2); // Montant du paiement partiel
            $table->decimal('montant_restant', 10, 2)->default(0); // Montant restant après ce paiement
            $table->string('mode')->default('especes'); // Mode de paiement (carte, espèces, etc.)
            $table->date('date_paiement'); // Date du paiement
            $table->text('notes')->nullable(); // Notes sur le paiement
            $table->boolean('est_solde')->default(false); // Si ce paiement solde la créance
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Qui a enregistré le paiement
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
