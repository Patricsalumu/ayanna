<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Afficher la liste des utilisateurs d'une entreprise
    public function show($entreprise)
    {
        $entreprise = Entreprise::findOrFail($entreprise);
        $users = $entreprise->users()->get();
        return view('users.show', compact('users', 'entreprise'));
    }

    // Afficher le formulaire de création
    public function create($entreprise)
    {
        $entreprise = Entreprise::findOrFail($entreprise);
        return view('users.create', compact('entreprise'));
    }

    // Enregistrer un nouvel utilisateur
    public function store(Request $request, $entreprise)
    {
        $entreprise = Entreprise::findOrFail($entreprise);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => ['required', Rule::in(['super_admin','admin','comptoiriste','cuisinière','serveuse'])],
        ]);
        $validated['entreprise_id'] = $entreprise->id;
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);
        return redirect()->route('users.show', $entreprise->id)->with('success', 'Utilisateur ajouté avec succès.');
    }

    // Afficher le formulaire d'édition
    public function edit($entreprise, $user)
    {
        $entreprise = Entreprise::findOrFail($entreprise);
        $user = User::findOrFail($user);
        return view('users.edit', compact('user', 'entreprise'));
    }

    // Mettre à jour un utilisateur
    public function update(Request $request, $entreprise, $user)
    {
        $user = User::findOrFail($user);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','email',Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['super_admin','admin','comptoiriste','cuisinière','serveuse'])],
        ]);
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }
        $user->update($validated);
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
