<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('modes_paiement', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // ex : Espèces, Compte client, Carte, Mobile money, etc.
            $table->boolean('actif')->default(true);
            $table->unsignedBigInteger('entreprise_id')->nullable();
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('modes_paiement');
    }
};
