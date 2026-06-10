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
        Schema::create('points_de_vente', function (Blueprint $table) {
            $table->id();
            $table ->string('nom');
            $table->unsignedBigInteger('module_id')->nullable();
            $table->unsignedBigInteger('entreprise_id')->nullable();
            $table->enum('etat', ['ouvert', 'ferme'])->default('ferme');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_de_vente');
    }
};
