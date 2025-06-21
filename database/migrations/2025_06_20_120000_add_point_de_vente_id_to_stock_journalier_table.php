<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('stock_journalier', function (Blueprint $table) {
            $table->unsignedBigInteger('point_de_vente_id')->after('id');
            $table->foreign('point_de_vente_id')->references('id')->on('points_de_vente')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('stock_journalier', function (Blueprint $table) {
            $table->dropForeign(['point_de_vente_id']);
            $table->dropColumn('point_de_vente_id');
        });
    }
};
