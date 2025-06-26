<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecritures_comptables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journal_comptable')->onDelete('cascade');
            $table->foreignId('compte_id')->constrained('comptes')->onDelete('cascade');
            
            $table->string('libelle');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            
            // Références optionnelles
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('produit_id')->nullable()->constrained('produits')->onDelete('set null');
            
            $table->integer('ordre')->default(1); // Ordre des écritures dans le journal
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['journal_id', 'ordre']);
            $table->index(['compte_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecritures_comptables');
    }
};
