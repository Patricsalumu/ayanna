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
        Schema::create('classes_comptables', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 10); // 1, 2, 3, 4, 5, 6, 7 ou sous-classes 60, 601, etc.
            $table->string('nom'); // "Comptes de capitaux", "Comptes de charges", etc.
            $table->text('description')->nullable();
            $table->enum('type_document', ['bilan', 'resultat']); // Pour générer bilan ou compte de résultat
            $table->enum('type_nature', ['actif', 'passif', 'charge', 'produit']); // Nature comptable
            $table->boolean('est_principale')->default(false); // Classes principales 1-7 vs sous-classes
            $table->string('classe_parent', 10)->nullable(); // Pour les sous-classes : 601 parent = 6
            $table->integer('ordre_affichage')->default(0); // Pour l'ordre dans les rapports
            $table->unsignedBigInteger('entreprise_id'); // Ajout de la clé étrangère
            $table->timestamps();
            
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->unique(['numero', 'entreprise_id']); // Unique par entreprise
            $table->index(['type_document']);
            $table->index(['type_nature']);
            $table->index(['est_principale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes_comptables');
    }
};
