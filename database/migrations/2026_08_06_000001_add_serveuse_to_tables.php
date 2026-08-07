<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_restos', function (Blueprint $table) {
            if (!Schema::hasColumn('table_restos', 'serveuse_id')) {
                $table->foreignId('serveuse_id')->nullable()->after('salle_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('table_restos', function (Blueprint $table) {
            if (Schema::hasColumn('table_restos', 'serveuse_id')) {
                $table->dropConstrainedForeignId('serveuse_id');
            }
        });
    }
};
