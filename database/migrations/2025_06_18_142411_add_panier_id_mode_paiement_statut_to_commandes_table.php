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
        Schema::table('commandes', function (Blueprint $table) {
        // $table->unsignedBigInteger('panier_id')->nullable();
        // $table->string('mode_paiement')->nullable();
        // $table->foreign('panier_id')->references('id')->on('paniers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            //
             $table->dropForeign(['panier_id']);
             $table->dropColumn(['panier_id', 'mode_paiement']);
        });
    }
};
