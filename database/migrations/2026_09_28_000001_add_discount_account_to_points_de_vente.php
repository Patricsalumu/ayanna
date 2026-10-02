<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('points_de_vente', function (Blueprint $table) {
            $table->foreignId('compte_remise_id')->nullable()->constrained('comptes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('points_de_vente', function (Blueprint $table) {
            $table->dropConstrainedForeignId('compte_remise_id');
        });
    }
};