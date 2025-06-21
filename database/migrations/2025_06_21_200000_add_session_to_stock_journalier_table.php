<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_journalier', function (Blueprint $table) {
            $table->string('session', 20)->nullable()->after('date')->index();
        });
    }

    public function down()
    {
        Schema::table('stock_journalier', function (Blueprint $table) {
            $table->dropColumn('session');
        });
    }
};
