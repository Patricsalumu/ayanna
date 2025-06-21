<x-app-layout>
    <x-slot name="header">
            <!-- Menu supérieur -->
    <nav class="mb-6 border-b pb-4 flex flex-wrap gap-4 text-sm font-medium text-gray-700">
    <a href="{{ route('pointsDeVente.show', $entreprise->id) }}" class="hover:text-blue-600">Points de Vente</a>
    <a href="{{ route('salles.show', $entreprise->id) }}" class="hover:text-blue-600">Plans de Salle</a>
    <a href="{{ route('categories.show', ['entreprise' => $entreprise->id, 'module_id' => request('module_id')]) }}" class="hover:text-blue-600">Catégories</a>
    <a href="{{ route('produits.entreprise', $entreprise->id) }}" class="hover:text-blue-600">Produits</a>
    <a href="#" class="hover:text-blue-600">Commandes</a>
    <a href="{{ route('clients.show', $entreprise->id) }}" class="hover:text-blue-600">Clients</a>
    <a href="{{ route('users.show', $entreprise->id) }}" class="hover:text-blue-600">Utilisateurs</a>
    <a href="{{ route('comptes.index') }}" class="hover:text-blue-600 text-green-700 font-bold">Comptes</a>
    </nav>
    </x-slot>
<div class="p-6">
    <!-- Barre de contrôle -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="flex gap-2 w-full sm:w-auto">
            <input type="text" placeholder="Rechercher..." class="px-4 py-2 border rounded-md shadow-sm w-full sm:w-64">
        </div>
        <div>
            <button id="toggleView" class="bg-gray-200 text-gray-800 px-3 py-1 rounded-md text-sm hover:bg-gray-300">
                Basculer Vue
            </button>
        </div>
    </div>

    @if(isset($module) && $module)
        <div class="flex justify-center mb-8">
            <a href="{{ route('pointsDeVente.create', [$entreprise->id, 'module_id' => $module->id]) }}"
               class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 flex items-center gap-2 shadow text-lg font-semibold">
                <span class="text-2xl">➕</span> Ajouter un point de vente
            </a>
        </div>
    @endif

    <!-- Affichage des points de vente -->
    <div id="gridView" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($pointsDeVente as $pdv)
        <div class="bg-white shadow rounded-xl p-4 flex flex-col justify-between relative">
            <!-- Bouton trois points -->
            <div class="absolute top-2 right-2" x-data="{ open: false }">
                <button @click="open = !open" class="p-1 rounded-full hover:bg-gray-200 focus:outline-none">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="5" r="1.5"/>
                        <circle cx="12" cy="12" r="1.5"/>
                        <circle cx="12" cy="19" r="1.5"/>
                    </svg>
                </button>
                <!-- Menu déroulant -->
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg z-10">
                    <form action="{{ route('pointsDeVente.duplicate', [$entreprise->id, $pdv->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dupliquer</button>
                    </form>
                    <a href="{{ route('pointsDeVente.edit', [$entreprise->id, $pdv->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Modifier</a>
                    @if($pdv->etat === 'ouvert')
                        @if(!$pdv->hasPanierEnCours())
                            <form action="{{ route('stock_journalier.fermer_session', $pdv->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded-md text-sm hover:bg-red-700 ml-2">Fermer</button>
                            </form>
                        @else
                            <span class="text-xs text-red-500 block px-4 py-2">Impossible de fermer : panier en cours</span>
                        @endif
                    @endif                    
                    <form action="{{ route('pointsDeVente.destroy', [$entreprise->id, $pdv->id]) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Supprimer</button>
                    </form>
                </div>
            </div>
            <div>
                <h2 class="text-lg font-bold mb-2">{{ $pdv->nom }}</h2>
                <p class="text-sm text-gray-600">Fermeture : {{ now()->format('d/m/Y') }}</p>
                <p class="text-sm text-gray-600">Solde : 0,00 Fr</p>
                @if($pdv->etat === 'ferme')
                    <a href="{{ route('vente.ouvrir', $pdv->id) }}" class="mt-4 inline-block bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                        Ouvrir la vente
                    </a>
                @else
                    <a href="{{ route('vente.continuer', $pdv->id) }}" class="mt-4 inline-block bg-purple-700 text-white px-4 py-2 rounded-md text-sm hover:bg-purple-800">
                        Continuer la vente
                    </a>
                @endif
            </div>
        </div>
        @endforeach
    </div> <!-- fermeture correcte de gridView -->
    <div id="listView" class="hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-auto bg-white shadow rounded-lg">
                <thead class="bg-gray-100 text-left text-sm font-medium text-gray-600">
                    <tr>
                        <th class="p-3">Nom</th>
                        <th class="p-3">Ouverture</th>
                        <th class="p-3">Fermeture</th>
                        <th class="p-3">Solde</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pointsDeVente as $pdv)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 font-semibold">{{ $pdv->nom }}</td>
                        <td class="p-3">
                            @php
                                $lastOuverture = $pdv->historiques()->where('etat', 'ouvert')->latest('opened_at')->first();
                            @endphp
                            @if($lastOuverture && $lastOuverture->opened_at)
                                Ouvert le {{ \Carbon\Carbon::parse($lastOuverture->opened_at)->format('d/m/Y H:i') }}
                                @if($lastOuverture->opened_by)
                                    par {{ optional($lastOuverture->openedBy)->name }}
                                @elseif($lastOuverture->user)
                                    par {{ $lastOuverture->user->name }}
                                @endif
                            @endif
                        </td>
                        <td class="p-3">
                            @php
                                $lastFermeture = $pdv->historiques()->where('etat', 'ferme')->latest('closed_at')->first();
                            @endphp
                            @if($lastFermeture && $lastFermeture->closed_at)
                                Fermé le {{ \Carbon\Carbon::parse($lastFermeture->closed_at)->format('d/m/Y H:i') }}
                                @if($lastFermeture->closed_by)
                                    par {{ optional($lastFermeture->closedBy)->name }}
                                @elseif($lastFermeture->user)
                                    par {{ $lastFermeture->user->name }}
                                @endif
                            @endif
                        </td>
                        <td class="p-3">
                            @php
                                $solde = $lastFermeture ? number_format($lastFermeture->solde, 0, ',', ' ') : '0';
                            @endphp
                            {{ $solde }} Fr
                        </td>
                        <td class="p-3 flex gap-2">
                            @if($pdv->etat === 'ferme')
                                <a href="{{ route('vente.ouvrir', $pdv->id) }}" class="bg-green-600 text-white px-3 py-1 rounded-md text-sm hover:bg-green-700">Ouvrir la vente</a>
                            @else
                                <a href="{{ route('vente.continuer', $pdv->id) }}" class="bg-purple-600 text-white px-3 py-1 rounded-md text-sm hover:bg-purple-700">Continuer la vente</a>
                                <form action="{{ route('stock_journalier.fermer_session', $pdv->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded-md text-sm hover:bg-red-700 ml-2">Fermer</button>
                                </form>
                            @endif
                            <form action="{{ route('pointsDeVente.duplicate', [$entreprise->id, $pdv->id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="bg-purple-600 text-white px-3 py-1 rounded-md text-sm hover:bg-purple-700">
                                    Dupliquer
                                </button>
                            </form>
                            <a href="{{ route('pointsDeVente.edit', [$entreprise->id, $pdv->id]) }}" class="bg-red-600 text-white px-3 py-1 rounded-md text-sm hover:bg-red-700">Modifier</a>
                            <form action="{{ route('pointsDeVente.destroy', [$entreprise->id, $pdv->id]) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded-md text-sm hover:bg-red-700">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    </div>

<script>
    const toggleViewBtn = document.getElementById('toggleView');
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');

    toggleViewBtn.addEventListener('click', () => {
        gridView.classList.toggle('hidden');
        listView.classList.toggle('hidden');
    });

    const searchInput = document.querySelector('input[placeholder="Rechercher..."]');

    searchInput.addEventListener('input', function () {
        const value = this.value.trim().toLowerCase();

        // Filtrage pour la vue en carte
        gridView.querySelectorAll('.bg-white.shadow.rounded-xl').forEach(card => {
            const title = card.querySelector('h2').textContent.trim().toLowerCase();
            card.style.display = title.startsWith(value) ? '' : 'none';
        });

        // Filtrage pour la vue en liste
        listView.querySelectorAll('tbody tr').forEach(row => {
            const cell = row.querySelector('td');
            if (!cell) return;
            const text = cell.textContent.trim().toLowerCase();
            row.style.display = text.startsWith(value) ? '' : 'none';
        });
    });
</script>
</x-app-layout>
