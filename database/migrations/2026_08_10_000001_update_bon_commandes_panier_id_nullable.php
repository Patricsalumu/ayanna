<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bon_commandes', function (Blueprint $table) {
            $table->dropForeign(['panier_id']);
        });

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE bon_commandes ALTER COLUMN panier_id DROP NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite does not support modifying column nullability by ALTER TABLE.
            // If the database uses SQLite, migrate manually or recreate the table.
            throw new \RuntimeException('SQLite does not support altering column nullability in this migration.');
        } else {
            DB::statement('ALTER TABLE bon_commandes MODIFY panier_id BIGINT UNSIGNED NULL');
        }

        Schema::table('bon_commandes', function (Blueprint $table) {
            $table->string('entreprise_nom')->nullable()->after('utilisateur_id');
            $table->string('point_de_vente_nom')->nullable()->after('entreprise_nom');
            $table->string('table_numero')->nullable()->after('point_de_vente_nom');
        });

        Schema::table('bon_commandes', function (Blueprint $table) {
            $table->foreign('panier_id')->references('id')->on('paniers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_commandes', function (Blueprint $table) {
            $table->dropForeign(['panier_id']);
        });

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE bon_commandes ALTER COLUMN panier_id SET NOT NULL');
        } elseif ($driver === 'sqlite') {
            throw new \RuntimeException('SQLite does not support altering column nullability in this migration.');
        } else {
            DB::statement('ALTER TABLE bon_commandes MODIFY panier_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('bon_commandes', function (Blueprint $table) {
            $table->dropColumn(['entreprise_nom', 'point_de_vente_nom', 'table_numero']);
        });

        Schema::table('bon_commandes', function (Blueprint $table) {
            $table->foreign('panier_id')->references('id')->on('paniers')->onDelete('cascade');
        });
    }
};
