@extends('layouts.app2', ['withNavigation' => true])

@section('content')
<!-- CONTENU DE LA PAGE SEULEMENT -->
<main class="flex-1 px-1 sm:px-2 lg:px-4 py-2">
    <div class="flex items-center justify-center space-x-4 my-6">
        <!-- Barre de recherche -->
        <div class="relative hidden sm:block">
            <input id="searchInput"
                placeholder="Rechercher..."
                class="w-64 rounded-full border border-gray-300 pl-4 pr-10 py-2 focus:outline-none focus:border-gray-400" />
            <span class="absolute right-3 top-2.5 text-gray-400">🔍</span>
        </div>

        <!-- Boutons de bascule de vue -->
        <div class="flex space-x-2">
            <!-- Bouton Vue Grille -->
            <button id="btnGridView"
                    title="Vue Grille"
                    class="bg-gray-100 rounded p-2 hover:bg-gray-200 text-gray-600 flex items-center justify-center focus:bg-indigo-100 focus:text-indigo-700">
                    <i data-lucide="grid"></i>
            </button>
            <!-- Bouton Vue Liste -->
            <button id="btnListView"
                    title="Vue Liste"
                    class="bg-gray-100 rounded p-2 hover:bg-gray-200 text-gray-600 flex items-center justify-center focus:bg-indigo-100 focus:text-indigo-700">
                <i data-lucide="list"></i>
            </button>
        </div>
    </div>

    <!-- VUE EN GRILLE -->
    <div id="gridView" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($pointsDeVente as $pdv)
            <!-- CONTENU DE CHAQUE CARD DE PDV EN GRILLE-->
            <div class="bg-white rounded-lg shadow p-4 relative hover:shadow-lg transition">
                <div class="absolute top-2 right-2" x-data="{ open: false, showEdit: false, editFormHtml: '', loadingEdit: false, showDelete: false, showDuplicate: false, duplicateFormHtml: '', loadingDuplicate: false }">
                    <button @click="open = !open" class="p-1 rounded-full hover:bg-gray-100 flex flex-col items-center justify-center">
                        <span class="block w-1 h-1 bg-gray-700 rounded-full mb-0.5"></span>
                        <span class="block w-1 h-1 bg-gray-700 rounded-full mb-0.5"></span>
                        <span class="block w-1 h-1 bg-gray-700 rounded-full"></span>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white rounded shadow-lg z-10">
                        <button type="button"
                            class="w-full text-left px-4 py-2 hover:bg-gray-100"
                            @click.stop="open = false; showEdit = true; loadingEdit = true; fetch('{{ route('pointsDeVente.edit', [$entreprise->id, $pdv->id]) }}', {headers: { 'X-Requested-With': 'XMLHttpRequest' }}).then(r => r.text()).then(html => { editFormHtml = html; loadingEdit = false; })">
                            Modifier
                        </button>
                        <button type="button"
                            class="w-full text-left px-4 py-2 hover:bg-gray-100"
                            @click.stop="open = false; loadingDuplicate = true; fetch('{{ route('pointsDeVente.duplicate', [$entreprise->id, $pdv->id]) }}', {method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }}).then(r => r.json()).then(data => { loadingDuplicate = false; if(data.success && data.edit_url){ showEdit = true; loadingEdit = true; fetch(data.edit_url, {headers: { 'X-Requested-With': 'XMLHttpRequest' }}).then(r2 => r2.text()).then(html => { editFormHtml = html; loadingEdit = false; }); } else { alert(data.error || 'Erreur lors de la duplication'); } })">
                            Dupliquer
                        </button>
                        @if($pdv->etat === 'ouvert')
                            @if(!$pdv->hasPanierEnCours())
                                <form method="POST" action="{{ route('vente.fermer', $pdv->id) }}" onsubmit="return confirm('Confirmer la fermeture de ce point de vente ?');" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Fermer
                                    </button>
                                </form>
                            @else
                                <button type="button" class="bg-gray-300 text-gray-600 px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow cursor-not-allowed" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Fermer
                                </button>
                            @endif
                        @endif
                        <button type="button"
                            class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600"
                            @click.stop="open = false; showDelete = true">
                            Supprimer
                        </button>
                    </div>
                    <!-- MODALE EDITION POINT DE VENTE -->
                    <div x-show="showEdit" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                        <div @click.away="showEdit = false" class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 relative">
                            <div class="flex items-center justify-between mb-4">
                                <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 rounded-full shadow border-2 border-white bg-white">
                                <button @click="showEdit = false" class="text-gray-400 hover:text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <h2 class="text-lg font-bold mb-4 text-gray-700">Modifier le point de vente</h2>
                            <template x-if="loadingEdit">
                                <div class="text-center py-8 text-gray-500">Chargement...</div>
                            </template>
                            <div x-html="editFormHtml"></div>
                        </div>
                    </div>
                    <!-- MODALE DUPLICATION POINT DE VENTE -->
                    <div x-show="showDuplicate" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                        <div @click.away="showDuplicate = false" class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 relative">
                            <div class="flex items-center justify-between mb-4">
                                <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 rounded-full shadow border-2 border-white bg-white">
                                <button @click="showDuplicate = false" class="text-gray-400 hover:text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <h2 class="text-lg font-bold mb-4 text-gray-700">Dupliquer le point de vente</h2>
                            <template x-if="loadingDuplicate">
                                <div class="text-center py-8 text-gray-500">Chargement...</div>
                            </template>
                            <div x-html="duplicateFormHtml"></div>
                        </div>
                    </div>
                    <!-- MODALE DE CONFIRMATION SUPPRESSION -->
                    <div x-show="showDelete" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6 relative text-center animate-fade-in">
                            <div class="flex items-center justify-between mb-4">
                                <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 rounded-full shadow border-2 border-white bg-white">
                                <button @click="showDelete = false" class="text-gray-400 hover:text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <div class="flex flex-col items-center justify-center">
                                <div class="bg-red-100 rounded-full p-3 mb-3 mt-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 mb-2">Confirmer la suppression</h3>
                                <p class="text-gray-600 mb-4">Voulez-vous vraiment supprimer le point de vente <span class="font-semibold">{{ $pdv->nom }}</span> ?<br><span class="text-xs text-gray-400">Cette action est irréversible.</span></p>
                                <form method="POST" action="{{ route('pointsDeVente.destroy', [$entreprise->id, $pdv->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <div class="flex justify-center gap-3 mt-2">
                                        <button type="button" @click="showDelete = false" class="px-4 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">Annuler</button>
                                        <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Supprimer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <h2 class="text-lg font-bold text-gray-800">{{ $pdv->nom }}</h2>
                @if($pdv->etat === 'ferme')
                    @php
                        $lastFermeture = $pdv->historiques()->where('etat','ferme')->latest('closed_at')->first();
                        $soldeFermeture = $lastFermeture ? number_format($lastFermeture->solde, 0, ',', ' ') : '0';
                    @endphp
                    <p class="text-gray-600 text-sm">Fermé le {{ $lastFermeture && $lastFermeture->closed_at ? \Carbon\Carbon::parse($lastFermeture->closed_at)->format('d/m/Y H:i') : '-' }}</p>
                    <p class="text-gray-600 text-sm">Solde à la fermeture : {{ $soldeFermeture }} Fr</p>
                    <a href="{{ route('vente.ouvrir', $pdv->id) }}" class="mt-3 block text-center bg-green-600 text-white rounded py-2 hover:bg-green-700">Ouvrir la vente</a>
                @else
                    @php
                        $lastOuverture = $pdv->historiques()->where('etat','ouvert')->latest('opened_at')->first();
                        $soldeEnCours = $pdv->getSoldeEnCours() ?? 0;
                    @endphp
                    <p class="text-gray-600 text-sm">Ouvert le {{ $lastOuverture && $lastOuverture->opened_at ? \Carbon\Carbon::parse($lastOuverture->opened_at)->format('d/m/Y H:i') : '-' }}</p>
                    <p class="text-gray-600 text-sm">Solde en cours : {{ number_format($soldeEnCours, 0, ',', ' ') }} Fr</p>
                    <a href="{{ route('vente.continuer', $pdv->id) }}" class="mt-3 block text-center bg-purple-600 text-white rounded py-2 hover:bg-purple-700">Continuer la vente</a>
                @endif
            </div>
        @endforeach
    </div>


    <!-- VUE EN LISTE -->
        <div id="listView" class="hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-auto rounded-lg bg-white shadow">
                    <thead class="bg-gray-100 text-gray-600 text-left text-sm font-medium">
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
                                @php $lastOuverture = $pdv->historiques()->where('etat','ouvert')->latest('opened_at')->first(); @endphp
                                @if($lastOuverture && $lastOuverture->opened_at) Ouvert le {{ \Carbon\Carbon::parse($lastOuverture->opened_at)->format('d/m/Y H:i') }} @endif
                            </td>
                            <td class="p-3">
                                @php $lastFermeture = $pdv->historiques()->where('etat','ferme')->latest('closed_at')->first(); @endphp
                                @if($lastFermeture && $lastFermeture->closed_at) Fermé le {{ \Carbon\Carbon::parse($lastFermeture->closed_at)->format('d/m/Y H:i') }} @endif
                            </td>
                            <td class="p-3">
                                @php $solde = $lastFermeture ? number_format($lastFermeture->solde, 0, ',', ' ') : '0' ; @endphp
                                {{ $solde }} Fr
                            </td>
                            <td class="p-3 flex space-x-2">
                                <div class="flex flex-row gap-2">
                                    <div x-data="{ showEdit: false, editFormHtml: '', loadingEdit: false }" class="inline">
                                        <button type="button"
                                                @click="showEdit = true; loadingEdit = true; fetch('{{ route('pointsDeVente.edit', [$entreprise->id, $pdv->id]) }}', {headers: { 'X-Requested-With': 'XMLHttpRequest' }}).then(r => r.text()).then(html => { editFormHtml = html; loadingEdit = false; })"
                                                class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                                            Modifier
                                        </button>
                                        <!-- MODALE EDITION POINT DE VENTE -->
                                        <div x-show="showEdit" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                                            <div @click.away="showEdit = false" class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 relative">
                                                <div class="flex items-center justify-between mb-4">
                                                    <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 rounded-full shadow border-2 border-white bg-white">
                                                    <button @click="showEdit = false" class="text-gray-400 hover:text-gray-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </div>
                                                <h2 class="text-lg font-bold mb-4 text-gray-700">Modifier le point de vente</h2>
                                                <template x-if="loadingEdit">
                                                    <div class="text-center py-8 text-gray-500">Chargement...</div>
                                                </template>
                                                <div x-html="editFormHtml"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-data="{ loadingDuplicate: false, showEdit: false, editFormHtml: '', loadingEdit: false }" class="inline">
                                        <button type="button"
                                                @click="loadingDuplicate = true; fetch('{{ route('pointsDeVente.duplicate', [$entreprise->id, $pdv->id]) }}', {method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }}).then(r => r.json()).then(data => { loadingDuplicate = false; if(data.success && data.edit_url){ showEdit = true; loadingEdit = true; fetch(data.edit_url, {headers: { 'X-Requested-With': 'XMLHttpRequest' }}).then(r2 => r2.text()).then(html => { editFormHtml = html; loadingEdit = false; }); } else { alert(data.error || 'Erreur lors de la duplication'); } })"
                                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17l4 4 4-4m-4-5v9" /></svg>
                                            Dupliquer
                                        </button>
                                        <!-- MODALE EDITION APRES DUPLICATION -->
                                        <div x-show="showEdit" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                                            <div @click.away="showEdit = false" class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 relative">
                                                <div class="flex items-center justify-between mb-4">
                                                    <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 rounded-full shadow border-2 border-white bg-white">
                                                    <button @click="showEdit = false" class="text-gray-400 hover:text-gray-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </div>
                                                <h2 class="text-lg font-bold mb-4 text-gray-700">Modifier le point de vente</h2>
                                                <template x-if="loadingEdit">
                                                    <div class="text-center py-8 text-gray-500">Chargement...</div>
                                                </template>
                                                <div x-html="editFormHtml"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if($pdv->etat === 'ferme')
                                    <a href="{{ route('vente.ouvrir', $pdv->id) }}"
                                       class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Ouvrir le point de vente
                                    </a>
                                @else
                                @if(!$pdv->hasPanierEnCours())
                                        <form method="POST" action="{{ route('vente.fermer', $pdv->id) }}" onsubmit="return confirm('Confirmer la fermeture de ce point de vente ?');" class="inline">
                                    @csrf
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Fermer
                                        </button>
                                        </form>
                                    @else
                                        <button type="button" class="bg-gray-300 text-gray-600 px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow cursor-not-allowed" disabled>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Fermer
                                        </button>
                                @endif
                                    <a href="{{ route('vente.continuer', $pdv->id) }}"
                                       class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4" /></svg>
                                        Continuer la vente
                                    </a>
                                @endif
                                <div x-data="{ showModal: false }" class="inline">
                                    <button type="button"
                                            @click="showModal = true"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1 shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        Supprimer
                                    </button>
                                    <!-- MODALE DE CONFIRMATION SUPPRESSION -->
                                    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                                        <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6 relative text-center animate-fade-in">
                                            <div class="flex items-center justify-between mb-4">
                                                <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 rounded-full shadow border-2 border-white bg-white">
                                                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </div>
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="bg-red-100 rounded-full p-3 mb-3 mt-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </div>
                                                <h3 class="text-lg font-bold text-gray-800 mb-2">Confirmer la suppression</h3>
                                                <p class="text-gray-600 mb-4">Voulez-vous vraiment supprimer le point de vente <span class="font-semibold">{{ $pdv->nom }}</span> ?<br><span class="text-xs text-gray-400">Cette action est irréversible.</span></p>
                                                <form method="POST" action="{{ route('pointsDeVente.destroy', [$entreprise->id, $pdv->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="flex justify-center gap-3 mt-2">
                                                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">Annuler</button>
                                                        <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Supprimer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                                 </tbody>
                </table>
            </div> 
        </div>
</main>
<!-- ========================= SCRIPTS ========================= -->
<script>
    const btnGridView = document.getElementById('btnGridView');
    const btnListView = document.getElementById('btnListView');
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const searchInput = document.getElementById('searchInput');

    function setActiveView(view) {
        if(view === 'grid') {
            btnGridView.classList.add('bg-indigo-100', 'text-indigo-700');
            btnListView.classList.remove('bg-indigo-100', 'text-indigo-700');
        } else {
            btnListView.classList.add('bg-indigo-100', 'text-indigo-700');
            btnGridView.classList.remove('bg-indigo-100', 'text-indigo-700');
        }
    }

    btnGridView?.addEventListener('click', () => {
        gridView.classList.remove('hidden');
        listView.classList.add('hidden');
        setActiveView('grid');
    });
    btnListView?.addEventListener('click', () => {
        listView.classList.remove('hidden');
        gridView.classList.add('hidden');
        setActiveView('list');
    });
    // Initial focus sur la vue grille
    setActiveView('grid');

    searchInput?.addEventListener('input', (e) => {
        const value = e.target.value.trim().toLowerCase();

        // Filtrage des cartes
        gridView.querySelectorAll('.bg-white.rounded-lg').forEach(card => {
            const title = card.querySelector('h2')?.textContent.trim().toLowerCase();
            card.style.display = title.startsWith(value) ? '' : 'none';
        });
        // Filtrage des lignes du tableau
        listView.querySelectorAll('tbody tr').forEach(row => {
            const title = row.querySelector('td')?.textContent.trim().toLowerCase();
            row.style.display = title.startsWith(value) ? '' : 'none';
        });
    });
</script>
@endsection
