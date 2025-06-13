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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_id')->constrained('categories')->onDelete('cascade'); // référence à la catégorie du produi
            $table->string('nom'); // nom du produit
            $table->string('image')->nullable();; // nom du produit
            $table->text('description')->nullable(); // description du produit
            $table->decimal('prix_achat', 10, 2); // prix d'achat du produit
            $table->decimal('prix_vente', 10, 2); // prix de vente du produit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
