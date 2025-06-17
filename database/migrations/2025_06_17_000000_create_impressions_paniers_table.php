<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('impressions_paniers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('panier_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('printed_at')->useCurrent();
            $table->decimal('total', 12, 2);
            $table->json('produits'); // snapshot des produits (id, nom, qte, prix, etc)
            $table->timestamps();

            $table->foreign('panier_id')->references('id')->on('paniers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
    public function down()
    {
        Schema::dropIfExists('impressions_paniers');
    }
};
