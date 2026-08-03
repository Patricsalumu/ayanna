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
            ->whereRaw('LOWER(nom) = ?', [Str::lower('Comptabilité')])
            ->value('id');

        if (! $moduleId) {
            $moduleId = DB::table('modules')->insertGetId([
                'nom' => 'Comptabilité',
                'description' => 'Module de gestion comptable et financière',
                'icon' => null,
                'disponible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $entrepriseIds = DB::table('entreprises')->pluck('id');

        foreach ($entrepriseIds as $entrepriseId) {
            $exists = DB::table('entreprise_module')
                ->where('entreprise_id', $entrepriseId)
                ->where('module_id', $moduleId)
                ->exists();

            if (! $exists) {
                DB::table('entreprise_module')->insert([
                    'entreprise_id' => $entrepriseId,
                    'module_id' => $moduleId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $moduleId = DB::table('modules')
            ->whereRaw('LOWER(nom) = ?', [Str::lower('Comptabilité')])
            ->value('id');

        if ($moduleId) {
            DB::table('entreprise_module')
                ->where('module_id', $moduleId)
                ->delete();

            DB::table('modules')->where('id', $moduleId)->delete();
        }
    }
};
