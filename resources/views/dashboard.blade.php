<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tableau de bord') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold">Bienvenue, {{ auth()->user()->name }} !</h1>
                <a href="{{ route('profile.edit') }}" class="text-indigo-600 hover:underline font-medium">Modifier le profil</a>
            </div>

            @if(auth()->user()->entreprise)
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 bg-white p-4 rounded shadow border border-gray-200">
                <div class="flex items-center space-x-3">
                    @if(auth()->user()->entreprise->logo)
                        <img src="{{ asset('storage/' . auth()->user()->entreprise->logo) }}" alt="Logo" class="w-12 h-12 object-contain rounded shadow">
                    @endif
                    <h2 class="text-lg font-bold mb-1 text-gray-700">{{ auth()->user()->entreprise->nom }}</h2>
                </div>
                <div class="flex space-x-2 mt-3 md:mt-0">
                    <a href="{{ route('entreprises.edit', auth()->user()->entreprise->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition font-medium">Modifier</a>
                    <form action="{{ route('entreprises.destroy', auth()->user()->entreprise->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition font-medium">Supprimer</button>
                    </form>
                    <a href="{{ route('entreprises.login', auth()->user()->entreprise->id) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition font-medium">Se connecter</a>
                </div>
            </div>
            @else
            <div class="flex justify-center my-8">
                <a href="{{ route('entreprises.create') }}" class="px-6 py-3 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 text-lg font-semibold transition">Créer mon entreprise</a>
            </div>
            @endif
        </div>
        <!-- Suppression de l'affichage des modules ici, déplacé dans entreprises/show.blade.php -->
    </div>
</x-app-layout>
