<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('pointsDeVente.show', $entreprise->id) }}" class="text-blue-600 hover:underline">&larr;</a>
            Clients
                <a href="{{ route('clients.create', $entreprise->id) }}" class="bg-blue-600 text-white px-0.5 py-0.5 rounded hover:bg-blue-700">
                    <span>➕</span>
                </a>
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
            @if(session('success'))
                <div class="mb-4 text-green-600">{{ session('success') }}</div>
            @endif
            <table class="w-full table-auto border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">#</th>
                        <th class="p-2 text-left">Nom</th>
                        <th class="p-2 text-left">Email</th>
                        <th class="p-2 text-left">Téléphone</th>
                        <th class="p-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $index => $client)
                        <tr class="border-t">
                            <td class="p-2">{{ $index+1 }}</td>
                            <td class="p-2">{{ $client->nom }}</td>
                            <td class="p-2">{{ $client->email }}</td>
                            <td class="p-2">{{ $client->telephone }}</td>
                            <td class="p-2 flex gap-2">
                                <a href="{{ route('clients.edit', [$entreprise->id, $client->id]) }}" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Modifier</a>
                                <form action="{{ route('clients.destroy', [$entreprise->id, $client->id]) }}" method="POST" onsubmit="return confirm('Supprimer ce client ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
