<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('modes_paiement', function (Blueprint $table) {
            $table->string('code')->nullable()->after('nom');
            $table->boolean('est_systeme')->default(false)->after('actif');
            $table->unsignedInteger('ordre')->default(0)->after('est_systeme');
            $table->index(['entreprise_id', 'actif']);
        });

        $defaults = [
            'especes' => ['nom' => 'Espèces', 'ordre' => 10],
            'compte_client' => ['nom' => 'Compte client', 'ordre' => 20],
            'offre' => ['nom' => 'Offre', 'ordre' => 30],
        ];

        foreach (DB::table('entreprises')->pluck('id') as $entrepriseId) {
            foreach ($defaults as $code => $default) {
                $mode = DB::table('modes_paiement')
                    ->where('entreprise_id', $entrepriseId)
                    ->where(function ($query) use ($code, $default) {
                        $query->where('code', $code)->orWhere('nom', $default['nom']);
                    })
                    ->first();

                if ($mode) {
                    DB::table('modes_paiement')->where('id', $mode->id)->update([
                        'code' => $code,
                        'est_systeme' => true,
                        'actif' => true,
                        'ordre' => $default['ordre'],
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('modes_paiement')->insert([
                        'entreprise_id' => $entrepriseId,
                        'code' => $code,
                        'nom' => $default['nom'],
                        'actif' => true,
                        'est_systeme' => true,
                        'ordre' => $default['ordre'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('modes_paiement', function (Blueprint $table) {
            $table->dropIndex(['entreprise_id', 'actif']);
            $table->dropColumn(['code', 'est_systeme', 'ordre']);
        });
    }
};