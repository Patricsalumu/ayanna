<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ajouter un client à {{ $entreprise->nom }}
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
            <form method="POST" action="{{ route('clients.store', $entreprise->id) }}">
                @csrf
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700" for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" required class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" value="{{ old('nom') }}">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700" for="email">Email</label>
                    <input type="email" name="email" id="email" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" value="{{ old('email') }}">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700" for="telephone">Téléphone</label>
                    <input type="text" name="telephone" id="telephone" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" value="{{ old('telephone') }}">
                </div>
                <div class="flex items-center justify-end">
                    <a href="{{ route('clients.show', $entreprise->id) }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 mr-2">Annuler</a>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
