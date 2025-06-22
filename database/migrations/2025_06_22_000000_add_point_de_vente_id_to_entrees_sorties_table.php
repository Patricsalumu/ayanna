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
        Schema::table('entrees_sorties', function (Blueprint $table) {
            $table->unsignedBigInteger('point_de_vente_id')->nullable()->after('id');
            $table->foreign('point_de_vente_id')->references('id')->on('points_de_vente')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entrees_sorties', function (Blueprint $table) {
            $table->dropForeign(['point_de_vente_id']);
            $table->dropColumn('point_de_vente_id');
        });
    }
};
