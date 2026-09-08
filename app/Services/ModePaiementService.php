<?php

namespace App\Services;

use App\Models\Entreprise;
use App\Models\ModePaiement;
use Illuminate\Support\Str;

class ModePaiementService
{
    public const DEFAULTS = [
        'especes' => ['nom' => 'Espèces', 'ordre' => 10],
        'compte_client' => ['nom' => 'Compte client', 'ordre' => 20],
        'offre' => ['nom' => 'Offre', 'ordre' => 30],
    ];

    public function ensureDefaults(Entreprise $entreprise): void
    {
        foreach (self::DEFAULTS as $code => $default) {
            $mode = ModePaiement::firstOrNew([
                'entreprise_id' => $entreprise->id,
                'code' => $code,
            ]);
            $isNew = !$mode->exists;
            $mode->nom = $mode->nom ?: $default['nom'];
            if ($isNew) {
                $mode->actif = true;
            }
            $mode->est_systeme = true;
            if (!$mode->ordre) {
                $mode->ordre = $default['ordre'];
            }
            $mode->save();
        }
    }

    public function actifs(Entreprise $entreprise)
    {
        $this->ensureDefaults($entreprise);

        return $entreprise->modesPaiement()
            ->where('actif', true)
            ->get()
            ->sortBy(function ($mode) {
                $priorite = array_search($mode->code, array_keys(self::DEFAULTS), true);

                return [
                    $priorite === false ? 1 : 0,
                    $priorite === false ? $mode->ordre : $priorite,
                    $mode->nom,
                ];
            })
            ->values();
    }

    public function codePourNom(string $nom): string
    {
        return Str::slug($nom, '_');
    }
}