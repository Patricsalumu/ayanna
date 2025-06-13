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
        Schema::create('salles', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // nom de la salle
            $table->unsignedBigInteger('entreprise_id')->nullable(); // ID de l'entreprise propriétaire de la salle
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('set null'); // Relation avec l'entreprise
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salles');
    }
};
