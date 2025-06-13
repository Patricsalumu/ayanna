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
     
        Schema::create('entreprises', function (Blueprint $table) {
        	$table->id();
        	$table->string('nom'); // nom de l'entreprise
        	$table->string('module')->nullable(); // ex : 'restaubar', 'stock', etc.
        	$table->string('telephone')->nullable(); // numéro de téléphone de l'entrepris
        	$table->string('email')->nullable()->unique();
        	$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entreprises');
    }
};

