
<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            Ajouter une salle à l’entreprise : {{ $entreprise->nom }}
        </h1>
    </x-slot>

    <div class="max-w-xl mx-auto p-6 bg-white rounded shadow">
        <form action="{{ route('salles.store', $entreprise->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="nom" class="block text-gray-700 font-semibold mb-2">Nom de la salle</label>
                <input type="text" name="nom" id="nom" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" value="{{ old('nom') }}" required>
                @error('nom')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('salles.show', $entreprise->id) }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Annuler</a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Ajouter</button>
            </div>
        </form>
    </div>
</x-app-layout>
