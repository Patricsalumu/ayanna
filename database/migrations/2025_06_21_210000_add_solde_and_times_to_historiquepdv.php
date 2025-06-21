<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('historiquepdv', function (Blueprint $table) {
            $table->decimal('solde', 12, 2)->nullable()->after('etat');
            $table->timestamp('opened_at')->nullable()->after('solde');
            $table->timestamp('closed_at')->nullable()->after('opened_at');
            $table->foreignId('opened_by')->nullable()->after('closed_at')->constrained('users');
            $table->foreignId('closed_by')->nullable()->after('opened_by')->constrained('users');
        });
    }
    public function down()
    {
        Schema::table('historiquepdv', function (Blueprint $table) {
            $table->dropColumn(['solde', 'opened_at', 'closed_at', 'opened_by', 'closed_by']);
        });
    }
};
