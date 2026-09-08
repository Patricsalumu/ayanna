<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Services\ModePaiementService;
use Illuminate\Http\Request;

class ModePaiementController extends Controller
{
    public function edit(Entreprise $entreprise, ModePaiementService $service)
    {
        $this->authorizeEntreprise($entreprise);
        $service->ensureDefaults($entreprise);

        return view('modes_paiement.edit', [
            'entreprise' => $entreprise,
            'modesPaiement' => $entreprise->modesPaiement()->get(),
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
        ]);

        foreach ($validated['modes'] ?? [] as $id => $values) {
            $mode = $entreprise->modesPaiement()->whereKey($id)->firstOrFail();
            $mode->nom = $values['nom'];
            $mode->ordre = (int) ($values['ordre'] ?? 0);
            $mode->actif = $mode->est_systeme ? true : (bool) ($values['actif'] ?? false);
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
        ]);

        $entreprise->modesPaiement()->create([
            'nom' => $validated['nom'],
            'code' => $service->codePourNom($validated['nom']).'_'.uniqid(),
            'actif' => true,
            'est_systeme' => false,
            'ordre' => (int) ($validated['ordre'] ?? 100),
        ]);

        return back()->with('success', 'Moyen de paiement ajouté.');
    }

    private function authorizeEntreprise(Entreprise $entreprise): void
    {
        abort_unless((int) auth()->user()->entreprise_id === (int) $entreprise->id, 403);
    }
}
