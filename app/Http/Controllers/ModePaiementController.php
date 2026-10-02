<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\Compte;
use App\Services\ModePaiementService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ModePaiementController extends Controller
{
    public function edit(Entreprise $entreprise, ModePaiementService $service)
    {
        $this->authorizeEntreprise($entreprise);
        $service->ensureDefaults($entreprise);
        $comptesParClasse = $this->comptesParClasse($entreprise);

        return view('modes_paiement.edit', [
            'entreprise' => $entreprise,
            'modesPaiement' => $entreprise->modesPaiement()->get(),
            'comptesParClasse' => $comptesParClasse,
            'comptesModesNouveaux' => $comptesParClasse['5']->merge($comptesParClasse['6']),
        ]);
    }

    public function update(Request $request, Entreprise $entreprise, ModePaiementService $service)
    {
        $this->authorizeEntreprise($entreprise);
        $service->ensureDefaults($entreprise);

        $validated = $request->validate([
            'modes' => 'nullable|array',
            'modes.*.actif' => 'nullable|boolean',
            'modes.*.ordre' => 'nullable|integer|min:0|max:999',
            'modes.*.nom' => 'required|string|max:100',
            'modes.*.compte_id' => 'required|integer',
        ]);

        $comptesParClasse = $this->comptesParClasse($entreprise);
        foreach ($validated['modes'] ?? [] as $id => $values) {
            $mode = $entreprise->modesPaiement()->whereKey($id)->firstOrFail();
            $classeCompte = $this->classeComptePourCode($mode->code);
            if (!$comptesParClasse[$classeCompte]->contains('id', (int) $values['compte_id'])) {
                throw ValidationException::withMessages([
                    "modes.{$id}.compte_id" => "Le compte doit appartenir à la classe {$classeCompte} de cette entreprise.",
                ]);
            }

            $mode->nom = $values['nom'];
            $mode->ordre = (int) ($values['ordre'] ?? 0);
            $mode->actif = $mode->est_systeme ? true : (bool) ($values['actif'] ?? false);
            $mode->compte_id = (int) $values['compte_id'];
            $mode->save();
        }

        return back()->with('success', 'Moyens de paiement mis à jour.');
    }

    public function store(Request $request, Entreprise $entreprise, ModePaiementService $service)
    {
        $this->authorizeEntreprise($entreprise);
        $service->ensureDefaults($entreprise);

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'ordre' => 'nullable|integer|min:0|max:999',
            'compte_id' => 'required|integer',
        ]);

        $code = $service->codePourNom($validated['nom']).'_'.uniqid();
        $classeCompte = $this->classeComptePourCode($code);
        if (!$this->comptesParClasse($entreprise)[$classeCompte]->contains('id', (int) $validated['compte_id'])) {
            throw ValidationException::withMessages([
                'compte_id' => "Le compte doit appartenir à la classe {$classeCompte} de cette entreprise.",
            ]);
        }

        $entreprise->modesPaiement()->create([
            'nom' => $validated['nom'],
            'code' => $code,
            'actif' => true,
            'est_systeme' => false,
            'ordre' => (int) ($validated['ordre'] ?? 100),
            'compte_id' => (int) $validated['compte_id'],
        ]);

        return back()->with('success', 'Moyen de paiement ajouté.');
    }

    private function authorizeEntreprise(Entreprise $entreprise): void
    {
        abort_unless((int) auth()->user()->entreprise_id === (int) $entreprise->id, 403);
    }

    private function classeComptePourCode(string $code): string
    {
        if (str_starts_with($code, 'compte_client')) {
            return '4';
        }

        return str_starts_with($code, 'offre') ? '6' : '5';
    }

    private function comptesParClasse(Entreprise $entreprise)
    {
        $comptes = Compte::with('classeComptable')
            ->where('entreprise_id', $entreprise->id)
            ->whereHas('classeComptable', function ($query) {
                $query->where(function ($classes) {
                    $classes->where('numero', 'like', '4%')
                        ->orWhere('numero', 'like', '5%')
                        ->orWhere('numero', 'like', '6%');
                });
            })
            ->orderBy('numero')
            ->get();

        return collect(['4', '5', '6'])
            ->mapWithKeys(fn ($classe) => [$classe => $comptes->filter(fn ($compte) => str_starts_with($compte->classeComptable->numero, $classe))->values()])
            ->all();
    }
}
