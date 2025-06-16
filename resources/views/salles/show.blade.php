<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">
            <a href="{{ route('pointsDeVente.show', [$entreprise->id, $pointDeVente->id]) }}" class="text-blue-600 hover:underline">&larr;</a>
            Salles
        </h1>
    </x-slot>

    <div class="max-w-5xl mx-auto p-6">
        <div class="mb-4 flex gap-2">
            <a href="{{ route('salles.create', $entreprise->id) }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Nouvelle salle
            </a>
        </div>

        @if ($entreprise->salles->count() > 0)
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nom de la salle</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nombre de tables</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($entreprise->salles as $index => $salle)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-800 font-semibold">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800 flex items-center gap-2">
                                    <span>{{ $salle->nom }}</span>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-300">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 10a6 6 0 1112 0A6 6 0 014 10zm6-4a4 4 0 100 8 4 4 0 000-8z" /></svg>
                                        {{ $salle->tables->count() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    <span class="inline-block bg-gray-200 text-gray-700 rounded-full px-3 py-1 text-xs font-semibold shadow">{{ $salle->tables->count() }} tables</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800 flex gap-2">
                                    <a href="{{ route('salle.plan', [$entreprise->id, $salle->id]) }}"
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4" /></svg>
                                        Plan
                                    </a>
                                    <a href="{{ route('salles.edit', [$entreprise->id, $salle->id]) }}"
                                       class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                                        Modifier
                                    </a>
                                    <form action="{{ route('salles.destroy', [$entreprise->id, $salle->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Confirmer la suppression ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-gray-600 italic">
                Aucune salle n’a encore été enregistrée pour cette entreprise.
            </div>
        @endif
    </div>
</x-app-layout>
