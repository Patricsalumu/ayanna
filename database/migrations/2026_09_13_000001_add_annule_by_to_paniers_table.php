<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paniers', function (Blueprint $table) {
            if (!Schema::hasColumn('paniers', 'annule_by')) {
                $table->foreignId('annule_by')->nullable()->after('annule_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('paniers', function (Blueprint $table) {
            if (Schema::hasColumn('paniers', 'annule_by')) {
                $table->dropConstrainedForeignId('annule_by');
            }
        });
    }
};
