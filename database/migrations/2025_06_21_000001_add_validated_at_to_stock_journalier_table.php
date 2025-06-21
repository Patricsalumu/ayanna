<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_journalier', function (Blueprint $table) {
            $table->timestamp('validated_at')->nullable()->after('quantite_initiale');
        });
    }

    public function down(): void
    {
        Schema::table('stock_journalier', function (Blueprint $table) {
            $table->dropColumn('validated_at');
        });
    }
};
