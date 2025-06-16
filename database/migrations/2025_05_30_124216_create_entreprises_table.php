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
        	$table->string('logo')->nullable(); // chemin vers le logo de l'entreprise
        	$table->string('adresse')->nullable(); // adresse de l'entreprise
        	$table->string('ville')->nullable(); // ville de l'entreprise
        	$table->string('pays')->nullable(); // pays de l'entreprise
        	$table->string('slogan')->nullable(); // slogan de l'entreprise
        	$table->string('site_web')->nullable(); // site web de l'entreprise
        	$table->string('identifiant_fiscale')->nullable(); // identifiant fiscale de l'entreprise
        	$table->string('registre_commerce')->nullable(); // registre de commerce de l'entreprise
        	$table->string('numero_entreprise')->nullable(); // numéro d'entreprise
        	$table->string('numero_tva')->nullable(); // numéro de TVA de l'entreprise
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

