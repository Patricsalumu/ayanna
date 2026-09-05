<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Entreprise;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Afficher la liste des utilisateurs d'une entreprise
    public function show($entreprise)
    {
        $entreprise = Entreprise::findOrFail($entreprise);
        $users = $entreprise->users()->with('pointsDeVente')->get();
        $pointsDeVente = $entreprise->pointsDeVente()->orderBy('nom')->get();

        return view('users.show', compact('users', 'entreprise', 'pointsDeVente'));
    }

    // Afficher le formulaire de création
    public function create($entreprise)
    {
        $entreprise = Entreprise::findOrFail($entreprise);
        $pointsDeVente = $entreprise->pointsDeVente()->orderBy('nom')->get();

        return view('users.create', compact('entreprise', 'pointsDeVente'));
    }

    // Enregistrer un nouvel utilisateur
    public function store(Request $request, $entreprise)
    {
        $entreprise = Entreprise::findOrFail($entreprise);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'digits:4',
                'confirmed',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $isDuplicate = User::query()
                        ->whereNotNull('password')
                        ->get()
                        ->contains(fn ($user) => Hash::check((string) $value, (string) $user->password));

                    if ($isDuplicate) {
                        $fail('Ce mot de passe est déjà utilisé.');
                    }
                },
            ],
            'role' => ['required', Rule::in(['super_admin','admin','caissier','caissier1','caissier2','comptoiriste','cuisinière','serveuse','Administrateur','Caissier','Serveuse'])],
            'code_pin' => ['nullable','regex:/^\d{4}$/'],
            'point_de_vente_ids' => ['nullable', 'array'],
            'point_de_vente_ids.*' => [
                'integer',
                Rule::exists('points_de_vente', 'id')->where(fn ($query) => $query->where('entreprise_id', $entreprise->id)),
            ],
        ]);
        $validated['entreprise_id'] = $entreprise->id;
        $validated['password'] = Hash::make($validated['password']);
        if (!empty($validated['code_pin'])) {
            $validated['code_pin'] = $validated['code_pin'];
        }
        $pointDeVenteIds = $validated['point_de_vente_ids'] ?? [];
        unset($validated['point_de_vente_ids']);

        $user = User::create($validated);
        $user->pointsDeVente()->sync($pointDeVenteIds);

        return redirect()->route('users.show', $entreprise->id)->with('success', 'Utilisateur ajouté avec succès.');
    }

    // Afficher le formulaire d'édition
    public function edit($entreprise, $user)
    {
        $entreprise = Entreprise::findOrFail($entreprise);
        $user = User::with('pointsDeVente')->findOrFail($user);
        $pointsDeVente = $entreprise->pointsDeVente()->orderBy('nom')->get();

        return view('users.edit', compact('user', 'entreprise', 'pointsDeVente'));
    }

    // Mettre à jour un utilisateur
    public function update(Request $request, $entreprise, $user)
    {
        $entreprise = Entreprise::findOrFail($entreprise);
        $user = User::findOrFail($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','email',Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'digits:4', 'confirmed'],
            'role' => ['required', Rule::in(['super_admin','admin','caissier','caissier1','caissier2','comptoiriste','cuisinière','serveuse','Administrateur','Caissier','Serveuse'])],
            'code_pin' => ['nullable','regex:/^\d{4}$/'],
            'point_de_vente_ids' => ['nullable', 'array'],
            'point_de_vente_ids.*' => [
                'integer',
                Rule::exists('points_de_vente', 'id')->where(fn ($query) => $query->where('entreprise_id', $entreprise->id)),
            ],
        ]);

        if ($request->filled('password')) {
            // Super admin peut réinitialiser le mot de passe sans ancien mot de passe.
            $validated['password'] = Hash::make((string) $request->password);
        }

        if ($request->has('code_pin')) {
            // Super admin peut modifier le code PIN sans ancien code.
            $validated['code_pin'] = $request->input('code_pin');
        }

        $pointDeVenteIds = $validated['point_de_vente_ids'] ?? [];
        unset($validated['point_de_vente_ids']);

        $user->update($validated);
        $user->pointsDeVente()->sync($pointDeVenteIds);

        return redirect()->route('users.show', $entreprise)->with('success', 'Utilisateur modifié avec succès.');
    }

    // Supprimer un utilisateur
    public function destroy($entreprise, $user)
    {
        $user = User::findOrFail($user);
        $user->delete();
        return redirect()->route('users.show', $entreprise)->with('success', 'Utilisateur supprimé avec succès.');
    }
}
