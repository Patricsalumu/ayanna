<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('pointsDeVente.show', [$entreprise->id, $pointDeVente->id]) }}" class="text-blue-600 hover:underline">&larr;</a>
            Produits
             <a href="{{ route('produits.create', $entreprise->id) }}" class="bg-blue-600 text-white px-0.5 py-0.5 rounded hover:bg-blue-700">
                <span class="text-1xl">➕</span>
            </a>
        </h2>
    </x-slot>

    <div class="p-6" x-data="{ grid: false }" x-ref="container">
        @php
            $sortUrl = function($field) use ($sort, $direction, $entreprise) {
                $newDirection = ($sort === $field && $direction === 'asc') ? 'desc' : 'asc';
                return route('produits.entreprise', [
                    'entreprise' => $entreprise->id,
                    'sort' => $field,
                    'direction' => $newDirection,
                    'search' => request('search')
                ]);
            };
        @endphp

        <!-- Barre de contrôle -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex gap-2 w-full sm:w-auto">
                <input id="searchInput" type="text" value="{{ request('search') }}" placeholder="Rechercher un produit..." class="px-4 py-2 border rounded-md shadow-sm w-full sm:w-64">
            </div>
            <div>
                <button
                    @click="
                        grid = !grid;
                        $nextTick(() => {
                            document.getElementById('searchInput').dispatchEvent(new Event('input'));
                        })
                    "
                    class="bg-gray-200 text-gray-800 px-3 py-1 rounded-md text-sm hover:bg-gray-300">
                    Basculer Vue
                </button>
            </div>
        </div>

        <!-- Bouton Ajouter -->
        <!-- <div class="flex justify-center mb-8">
            <a href="{{ route('produits.create', $entreprise->id) }}" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 flex items-center gap-2 shadow text-lg font-semibold">
                <span class="text-2xl">➕</span> Ajouter un produit
            </a>
        </div> -->

        <!-- Vue Liste -->
        <div x-show="!grid" id="listView" class="mt-6">
            <div id="produitsList">
                @include('produits.partials.list', ['produits' => $produits])
            </div>
        </div>

        <!-- Vue Grille -->
        <div x-show="grid" id="gridView">
            <div id="produitsGrid">
                @include('produits.partials.grid', ['produits' => $produits])
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const listView = document.getElementById('produitsList');
    const gridView = document.getElementById('produitsGrid');
    const alpineData = document.querySelector('[x-data]');

    function isGridActive() {
        return alpineData && alpineData.__x && alpineData.__x.$data && alpineData.__x.$data.grid;
    }

    searchInput.addEventListener('input', function() {
        const search = this.value;
        const params = new URLSearchParams(window.location.search);
        params.set('search', search);

        fetch("{{ route('produits.searchAjax', $entreprise->id) }}?" + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (isGridActive()) {
                gridView.innerHTML = data.grid;
            } else {
                listView.innerHTML = data.list;
            }
        });
    });
});
</script>
</x-app-layout>
