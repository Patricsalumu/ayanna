<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $moduleId = DB::table('modules')
            ->whereRaw('LOWER(nom) = ?', [Str::lower('POS Restaubar')])
            ->value('id');

        if (! $moduleId) {
            $moduleId = DB::table('modules')->insertGetId([
                'nom' => 'POS Restaubar',
                'description' => 'Module de gestion de restaurant et de salle',
                'icon' => null,
                'disponible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Le module POS Restaubar ne doit pas être activé automatiquement pour toutes les entreprises.
        // Il sera activé manuellement depuis l’interface des applications.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $moduleId = DB::table('modules')
            ->whereRaw('LOWER(nom) = ?', [Str::lower('POS Restaubar')])
            ->value('id');

        if ($moduleId) {
            DB::table('entreprise_module')
                ->where('module_id', $moduleId)
                ->delete();

            DB::table('modules')->where('id', $moduleId)->delete();
        }
    }
};
