<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entrees_sorties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compte_id')->constrained('comptes')->onDelete('cascade');
            $table->decimal('montant', 15, 2);
            $table->string('libele');
            $table->enum('type', ['credit', 'debit'])->default('credit');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('entrees_sorties');
    }
};
