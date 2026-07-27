<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('entrees_sorties', function (Blueprint $table) {
            if (!Schema::hasColumn('entrees_sorties', 'annule')) {
                $table->boolean('annule')->default(false)->after('comptabilise');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entrees_sorties', function (Blueprint $table) {
            if (Schema::hasColumn('entrees_sorties', 'annule')) {
                $table->dropColumn('annule');
            }
        });
    }
};
