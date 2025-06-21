<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('produits', function (Blueprint $table) {
            if (Schema::hasColumn('produits', 'point_de_vente_id')) {
                $table->dropForeign(['point_de_vente_id']);
                $table->dropColumn('point_de_vente_id');
            }
        });
    }

    public function down()
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->unsignedBigInteger('point_de_vente_id')->nullable()->after('categorie_id');
            $table->foreign('point_de_vente_id')->references('id')->on('points_de_vente')->onDelete('set null');
        });
    }
};
