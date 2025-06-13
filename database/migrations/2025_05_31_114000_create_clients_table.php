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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Nom du client
            $table->string('email')->nullable()->unique(); // Email du client
            $table->foreignId('entreprise_id')->constrained()->onDelete('cascade'); // Référence à l'entreprise du client
            $table->string('telephone')->nullable(); // Numéro de téléphone du client
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
