<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('paniers', function (Blueprint $table) {
            $table->timestamp('printed_at')->nullable()->after('status');
        });
    }
    public function down()
    {
        Schema::table('paniers', function (Blueprint $table) {
            $table->dropColumn('printed_at');
        });
    }
};
