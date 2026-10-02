<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_journalier', function (Blueprint $table) {
            $table->integer('quantite_abimee')->default(0)->after('quantite_vendue');
        });
    }

    public function down(): void
    {
        Schema::table('stock_journalier', function (Blueprint $table) {
            $table->dropColumn('quantite_abimee');
        });
    }
};
