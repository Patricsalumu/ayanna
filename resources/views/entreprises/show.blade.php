<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                @if($entreprise->logo)
                    <img src="{{ asset('storage/' . $entreprise->logo) }}" alt="Logo" class="w-14 h-14 object-contain rounded shadow bg-white border">
                @endif
                <span class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $entreprise->nom }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition font-medium">Profil</a>
                <a href="{{ route('entreprises.edit', $entreprise->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition font-medium">Profil entreprise</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition font-medium">Déconnexion</button>
                </form>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if(isset($modules) && $modules->count())
            <div class="my-10 flex flex-col items-center">
                <h3 class="text-2xl font-bold mb-6 text-gray-800 text-center">Applications disponibles</h3>
                <div class="w-full max-w-7xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6 justify-center">
                    @php
                        $modulesActives = $entreprise->modules->pluck('id')->toArray();
                    @endphp
                    @foreach($modules as $module)
                        <div class="group bg-white border border-gray-200 rounded-2xl shadow hover:shadow-lg transition p-6 flex flex-col items-center cursor-pointer hover:bg-indigo-50">
                            <div class="w-16 h-16 flex items-center justify-center mb-3">
                                @if($module->icon)
                                    <img src="{{ asset('storage/' . $module->icon) }}" alt="Icône du module" class="w-12 h-12 object-contain rounded-full shadow bg-white border">
                                @else
                                    <span class="text-4xl">🧩</span>
                                @endif
                            </div>
                            <span class="mt-2 text-sm font-medium text-center text-gray-800">{{ $module->nom }}</span>
                            <div class="text-xs text-gray-500 text-center mb-2 line-clamp-2">{{ $module->description }}</div>
                            @if($module->disponible)
                                @if(in_array($module->id, $modulesActives))
                                    <a href="{{ route('pointsDeVente.show', [$entreprise->id, 'module_id' => $module->id]) }}" class="mt-auto px-4 py-2 bg-green-500 text-white rounded-full text-sm font-medium shadow hover:bg-green-600 transition">
                                        Voir
                                    </a>
                                @else
                                    <form action="{{ route('modules.activate', $module->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mt-auto px-4 py-2 bg-green-500 text-white rounded-full text-sm font-medium shadow hover:bg-green-600 transition">
                                            Activer
                                        </button>
                                    </form>
                                @endif
                            @else
                                <button type="button" class="mt-auto px-4 py-2 bg-green-200 text-green-900 rounded-full text-sm font-medium shadow cursor-not-allowed" @click="openModal = true">
                                    Activer
                                </button>
                                <!-- Module indisponible -->
                                <div x-data="{ openModal: false }">
                                    <template x-if="openModal">
                                        <div class="fixed inset-0 flex items-center justify-center z-50">
                                            <div class="bg-black bg-opacity-40 absolute inset-0" @click="openModal = false"></div>
                                            <div class="bg-white rounded-lg shadow-lg p-6 z-10 max-w-sm w-full">
                                                <div class="flex items-center mb-4">
                                                    <span class="text-red-600 text-2xl mr-2">❌</span>
                                                    <span class="font-semibold text-red-700">Module non disponible</span>
                                                </div>
                                                <div class="mb-4 text-gray-700">
                                                    Ce module n'est pas encore disponible pour votre entreprise.
                                                </div>
                                                <button @click="openModal = false" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition w-full">
                                                    Fermer
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @endif
                            <div class="flex gap-2 mt-2">
                                <a href="{{ route('modules.edit', $module->id) }}" class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition text-xs flex items-center">
                                    ✏️ Modifier
                                </a>
                                <form action="{{ route('modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition text-xs flex items-center">
                                        🗑️ Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    <div class="flex justify-center mt-8 w-full">
                        <a href="{{ route('modules.create') }}" class="px-6 py-3 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 text-lg font-semibold transition flex items-center gap-2">
                            <span class="text-2xl">➕</span> Ajouter un module
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
@if(session('success') || session('error'))
    <div x-data="{ open: true }" x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-black bg-opacity-40 absolute inset-0"></div>
        <div class="bg-white rounded-lg shadow-lg p-6 z-10 max-w-sm w-full">
            <div class="flex items-center mb-4">
                @if(session('success'))
                    <span class="text-green-600 text-2xl mr-2">✔️</span>
                    <span class="font-semibold text-green-700">Succès</span>
                @else
                    <span class="text-red-600 text-2xl mr-2">❌</span>
                    <span class="font-semibold text-red-700">Erreur</span>
                @endif
            </div>
            <div class="mb-4 text-gray-700">
                {{ session('success') ?? session('error') }}
            </div>
            <button @click="open = false" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition w-full">
                Fermer
            </button>
        </div>
    </div>
@endif