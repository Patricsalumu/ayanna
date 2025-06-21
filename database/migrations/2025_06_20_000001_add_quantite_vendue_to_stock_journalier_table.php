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
        Schema::table('stock_journalier', function (Blueprint $table) {
            $table->integer('quantite_vendue')->default(0)->after('quantite_ajoutee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_journalier', function (Blueprint $table) {
            $table->dropColumn('quantite_vendue');
        });
    }
};
