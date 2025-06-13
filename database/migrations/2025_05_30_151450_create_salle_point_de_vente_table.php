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
        Schema::create('salle_point_de_vente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('point_de_vente_id')->constrained()->onDelete('cascade');
           $table->unsignedBigInteger('salle_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salle_point_de_vente');
    }
};
