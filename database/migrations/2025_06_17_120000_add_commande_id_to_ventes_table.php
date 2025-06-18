<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->unsignedBigInteger('commande_id')->nullable()->after('id');
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('set null');
        });
    }
    public function down()
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropForeign(['commande_id']);
            $table->dropColumn('commande_id');
        });
    }
};
