<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->foreignId('entreprise_id')->after('id')->constrained('entreprises')->onDelete('cascade');
        });
    }
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->dropColumn('entreprise_id');
        });
    }
};
