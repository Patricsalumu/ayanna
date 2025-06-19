<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('pointsDeVente.show', $entreprise->id) }}" class="text-blue-600 hover:underline">&larr;</a>
            Catégories
        </h2>
    </x-slot>

    <div class="p-6">
        <!-- Barre de contrôle -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex gap-2 w-full sm:w-auto">
                <input type="text" placeholder="Rechercher une catégorie..." class="px-4 py-2 border rounded-md shadow-sm w-full sm:w-64">
            </div>
            <div>
                <button id="toggleView" class="bg-gray-200 text-gray-800 px-3 py-1 rounded-md text-sm hover:bg-gray-300">
                    Basculer Vue
                </button>
            </div>
        </div>

        <!-- Bouton ajouter une catégorie centré -->
        <div class="flex justify-center mb-8">
            <a href="{{ route('categories.create', $entreprise->id) }}"
               class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 flex items-center gap-2 shadow text-lg font-semibold">
                <span class="text-2xl">➕</span> Ajouter une catégorie
            </a>
        </div>

        <!-- Vue grille -->
        <div id="gridView" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @forelse($categories as $categorie)
                <div class="bg-white rounded-xl shadow p-4 flex flex-col justify-between relative">
                    <div class="absolute top-2 right-2" x-data="{ open: false }">
                        <button @click="open = !open" class="p-1 rounded-full hover:bg-gray-200 focus:outline-none">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="5" r="1.5"/>
                                <circle cx="12" cy="12" r="1.5"/>
                                <circle cx="12" cy="19" r="1.5"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg z-10">
                            <a href="{{ route('categories.edit', [$entreprise->id, $categorie->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Modifier</a>
                            <form action="{{ route('categories.destroy', [$entreprise->id, $categorie->id]) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Supprimer</button>
                            </form>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold mb-2">{{ $categorie->nom }}</h3>
                </div>
            @empty
                <div class="col-span-full text-center text-gray-500">Aucune catégorie trouvée.</div>
            @endforelse
        </div>

        <!-- Vue liste -->
        <div id="listView" class="hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-auto bg-white shadow rounded-lg">
                    <thead class="bg-gray-100 text-left text-sm font-medium text-gray-600">
                        <tr>
                            <th class="p-3">Nom</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $categorie)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-3 font-semibold">{{ $categorie->nom }}</td>
                                <td class="p-3 flex gap-2">
                                    <a href="{{ route('categories.edit', [$entreprise->id, $categorie->id]) }}" class="bg-indigo-600 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-700">Modifier</a>
                                    <form action="{{ route('categories.destroy', [$entreprise->id, $categorie->id]) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded-md text-sm hover:bg-red-700">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-gray-500 p-4">Aucune catégorie trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Script toggle vue + recherche -->
    <script>
        const toggleViewBtn = document.getElementById('toggleView');
        const gridView = document.getElementById('gridView');
        const listView = document.getElementById('listView');

        toggleViewBtn.addEventListener('click', () => {
            gridView.classList.toggle('hidden');
            listView.classList.toggle('hidden');
        });

        const searchInput = document.querySelector('input[placeholder="Rechercher une catégorie..."]');
        searchInput.addEventListener('input', function () {
            const value = this.value.trim().toLowerCase();

            gridView.querySelectorAll('.bg-white.rounded-xl').forEach(card => {
                const title = card.querySelector('h3').textContent.trim().toLowerCase();
                card.style.display = title.startsWith(value) ? '' : 'none';
            });

            listView.querySelectorAll('tbody tr').forEach(row => {
                const cell = row.querySelector('td');
                if (!cell) return;
                const text = cell.textContent.trim().toLowerCase();
                row.style.display = text.startsWith(value) ? '' : 'none';
            });
        });
    </script>
</x-app-layout>
