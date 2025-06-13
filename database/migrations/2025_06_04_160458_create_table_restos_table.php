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
    Schema::create('table_restos', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('salle_id');
    $table->integer('numero');
    $table->string('forme');
    $table->integer('width')->default(100);
    $table->integer('height')->default(100);
    $table->integer('position_x')->default(0); // Position horizontale
    $table->integer('position_y')->default(0); // Position verticale
    $table->timestamps();

    $table->foreign('salle_id')->references('id')->on('salles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_restos');
    }
};
