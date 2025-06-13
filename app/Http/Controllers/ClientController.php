<?php
namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Entreprise;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function show(Entreprise $entreprise)
    {
        $clients = $entreprise->clients()->latest()->get();
        return view('clients.show', compact('entreprise', 'clients'));
    }

    public function create(Entreprise $entreprise)
    {
        return view('clients.create', compact('entreprise'));
    }

    public function store(Request $request, Entreprise $entreprise)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telephone' => 'nullable|string|max:20',
        ]);
        $validated['entreprise_id'] = $entreprise->id;
        Client::create($validated);
        return redirect()->route('clients.show', $entreprise->id)->with('success', 'Client ajouté avec succès');
    }

    public function edit(Entreprise $entreprise, Client $client)
    {
        return view('clients.edit', compact('entreprise', 'client'));
    }

    public function update(Request $request, Entreprise $entreprise, Client $client)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telephone' => 'nullable|string|max:20',
        ]);
        $client->update($validated);
        return redirect()->route('clients.show', $entreprise->id)->with('success', 'Client modifié avec succès');
    }

    public function destroy(Entreprise $entreprise, Client $client)
    {
        $client->delete();
        return redirect()->route('clients.show', $entreprise->id)->with('success', 'Client supprimé avec succès');
    }
}
