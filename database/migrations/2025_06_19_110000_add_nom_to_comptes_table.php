<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->string('nom')->after('id');
        });
    }
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn('nom');
        });
    }
};
