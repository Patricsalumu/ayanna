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
            $table->dropColumn(['utilisateur_id', 'client_id', 'point_de_vente_id', 'updated_at','table_id', 'date_commande']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
        $table->unsignedBigInteger('utilisateur_id');
        $table->unsignedBigInteger('client_id');
        $table->unsignedBigInteger('point_de_vente_id')->nullable();
        $table->unsignedBigInteger('table_id');
        $table->timestamp('updated_at');
        $table->date('date_commande');
        });
    }
};
