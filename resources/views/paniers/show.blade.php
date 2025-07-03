@extends('layouts.appvente')
@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto bg-white p-6 rounded-2xl shadow-lg">
        @if(session('success'))
            <div class="mb-4 text-green-600 font-bold text-center">{{ session('success') }}</div>
        @endif
        
        <div class="flex justify-between items-center mb-4">
            <div class="flex-1"></div>
            <h2 class="text-2xl font-bold text-gray-800 text-center flex-2">Tous les paniers</h2>
            <div class="flex-1 text-right">
                <a href="{{ route('paniers.historique-impressions') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                    </svg>
                    Historique des impressions
                </a>
            </div>
        </div>
        
        <!-- Filtres et recherche -->
        <div class="bg-gray-50 p-4 mb-6 rounded-xl shadow-inner">
            <form action="{{ route('paniers.show') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="point_de_vente" class="block text-sm font-medium text-gray-700 mb-1">Point de vente</label>
                        <select name="point_de_vente" id="point_de_vente" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Tous les points de vente</option>
                            @foreach($pointsDeVente as $pdv)
                                <option value="{{ $pdv->id }}" {{ request('point_de_vente') == $pdv->id ? 'selected' : '' }}>{{ $pdv->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                        <input type="date" id="date_debut" name="date_debut" value="{{ request('date_debut', now()->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                        <input type="date" id="date_fin" name="date_fin" value="{{ request('date_fin', now()->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select name="status" id="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Tous les statuts</option>
                            <option value="en_cours" {{ request('status') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                            <option value="payé" {{ request('status') == 'payé' ? 'selected' : '' }}>Payé</option>
                            <option value="annulé" {{ request('status') == 'annulé' ? 'selected' : '' }}>Annulé</option>
                            <option value="annulé" {{ request('status') == 'validé' ? 'selected' : '' }}>Validé</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch gap-4">
                    <div class="flex-grow">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                placeholder="Rechercher client, serveuse, table, produits..."
                                class="w-full border rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ request('search') }}"
                            />
                        </div>
                        <div class="mt-1 text-xs text-gray-500">Recherchez par nom de client, serveuse, numéro de table ou nom de produit</div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center justify-center gap-2 transition h-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filtrer
                        </button>
                        <a href="{{ route('paniers.show') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition h-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Statistiques -->
        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-16 bg-gradient-to-l from-blue-100 to-transparent opacity-50"></div>
                <div class="relative z-10">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-blue-700 font-medium">Total des paniers</div>
                            <div class="text-2xl font-bold">{{ $paniers->total() }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-xl p-4 border border-green-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-16 bg-gradient-to-l from-green-100 to-transparent opacity-50"></div>
                <div class="relative z-10">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-green-700 font-medium">Montant total</div>
                            <div class="text-2xl font-bold">{{ number_format($montantTotal, 0, ',', ' ') }} F</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-16 bg-gradient-to-l from-purple-100 to-transparent opacity-50"></div>
                <div class="relative z-10">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-purple-700 font-medium">Paniers payés</div>
                            <div class="text-2xl font-bold">{{ $paniersPayes }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-orange-50 rounded-xl p-4 border border-orange-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-16 bg-gradient-to-l from-orange-100 to-transparent opacity-50"></div>
                <div class="relative z-10">
                    <div class="flex items-center">
                        <div class="p-2 bg-orange-100 rounded-lg mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-orange-700 font-medium">Panier moyen</div>
                            <div class="text-2xl font-bold">{{ $paniers->count() > 0 ? number_format($montantTotal / $paniers->count(), 0, ',', ' ') : 0 }} F</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($paniers->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full table-auto rounded-xl overflow-hidden border">
                <thead class="bg-blue-100 text-gray-700">
                    <tr>
                        <th class="p-3 text-left">
                            <a href="{{ route('paniers.show', array_merge(request()->query(), ['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                ID
                                @if(request('sort') == 'id')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="p-3 text-left">
                            <a href="{{ route('paniers.show', array_merge(request()->query(), ['sort' => 'table_id', 'direction' => request('sort') == 'table_id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                Table
                                @if(request('sort') == 'table_id')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="p-3 text-left">
                            <a href="{{ route('paniers.show', array_merge(request()->query(), ['sort' => 'point_de_vente_id', 'direction' => request('sort') == 'point_de_vente_id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                Point de vente
                                @if(request('sort') == 'point_de_vente_id')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="p-3 text-left">Serveuse</th>
                        <th class="p-3 text-left">Client</th>
                        <th class="p-3 text-left">
                            <a href="{{ route('paniers.show', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                Date et heure
                                @if(request('sort') == 'created_at')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="p-3 text-left">
                            <a href="{{ route('paniers.show', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center">
                                Statut
                                @if(request('sort') == 'status')
                                    <
                                @endif
                            </a>
                        </th>
                        <th class="p-3 text-right">Montant</th>
                        <th class="p-3 text-center">Imprimé</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paniers as $panier)
                    <tr class="hover:bg-gray-100 {{ $panier->status === 'en_cours' ? 'cursor-pointer border-l-4 border-green-500' : ($panier->status === 'payé' ? 'border-l-4 border-blue-500' : 'border-l-4 border-red-500 opacity-60') }} panier-row"
                        data-url="{{ $panier->status === 'en_cours' ? route('vente.catalogue', ['pointDeVente' => $panier->point_de_vente_id]) . '?table_id=' . $panier->table_id : '' }}"
                        data-produits="{{ strtolower(collect($panier->produits)->pluck('nom')->implode(',')) }}">
                        <td class="p-3">
                            <div class="font-medium text-gray-900">#{{ $panier->id }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium">{{ $panier->tableResto->numero ?? 'Table ' . $panier->table_id }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium text-blue-600">{{ $panier->pointDeVente->nom ?? 'N/A' }}</div>
                        </td>
                        <td class="p-3">{{ $panier->serveuse->name ?? '-' }}</td>
                        <td class="p-3">{{ $panier->client->nom ?? '-' }}</td>
                        <td class="p-3">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $panier->created_at->format('d/m/Y') }}</span>
                                <span class="text-sm text-gray-500">{{ $panier->created_at->format('H:i') }}</span>
                            </div>
                        </td>
                        <td class="p-3">
                            @if($panier->status === 'en_cours')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>
                                    En cours
                                </span>
                            @elseif($panier->status === 'payé')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <span class="w-2 h-2 bg-blue-400 rounded-full mr-1"></span>
                                    Payé
                                </span>
                            @elseif($panier->status === 'validé')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <span class="w-2 h-2 bg-blue-400 rounded-full mr-1"></span>
                                    Validé
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <span class="w-2 h-2 bg-red-400 rounded-full mr-1"></span>
                                    Annulé
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-right font-medium">{{ number_format($panier->produits->sum(fn($p) => max(0, $p->pivot->quantite) * $p->prix_vente), 0, ',', ' ') }} F</td>
                        <td class="p-3 text-center">
                            @if($panier->is_printed)
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-800" title="Panier imprimé">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-400" title="Panier non imprimé">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                </span>
                            @endif
                        </td>
                        <td class="p-3 flex justify-center space-x-2">
                            <button class="bg-blue-600 text-white rounded-full p-2 hover:bg-blue-700 view-details-btn" 
                                title="Voir détails" 
                                data-panier-id="{{ $panier->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <button 
                                class="bg-gray-600 text-white rounded-full p-2 hover:bg-gray-700 print-receipt-btn" 
                                title="Imprimer reçu"
                                data-panier-id="{{ $panier->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $paniers->appends(request()->query())->links() }}
        </div>

        @else
            <div class="text-center py-12 bg-gray-50 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="text-xl font-medium text-gray-600 mb-2">Aucun panier trouvé</h3>
                <p class="text-gray-500">Essayez de modifier vos critères de recherche</p>
            </div>
        @endif
    </div>
</div>
<!-- Modale de détails du panier -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 transform transition-all duration-200 scale-95 my-8">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 mr-3">
                <h3 class="text-lg font-bold text-gray-900">Détails du panier</h3>
            </div>
            <button onclick="hideDetailsModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="p-6 max-h-[70vh] overflow-y-auto" id="panierDetailsContent">
            <div class="flex justify-center">
                <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Modale pour l'historique des impressions de paniers -->
<div id="historiqueImpressionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full mx-4 transform transition-all duration-200 scale-95 my-8">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                </svg>
                <h3 class="text-lg font-bold text-gray-900">Historique des impressions de paniers</h3>
            </div>
            <button onclick="hideHistoriqueImpressionsModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="p-6 max-h-[70vh] overflow-y-auto">
            <!-- Filtres -->
            <div class="bg-gray-50 p-4 mb-6 rounded-xl shadow-inner">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="date_debut_impressions" class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                        <input type="date" id="date_debut_impressions" name="date_debut_impressions" value="{{ now()->format('Y-m-d') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label for="date_fin_impressions" class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                        <input type="date" id="date_fin_impressions" name="date_fin_impressions" value="{{ now()->format('Y-m-d') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div class="flex items-end">
                        <button id="filtrerImpressions" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filtrer
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Contenu de l'historique -->
            <div id="historiqueImpressionsContent">
                <div class="flex justify-center">
                    <svg class="animate-spin h-10 w-10 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Pagination -->
            <div id="historiquePagination" class="mt-6 flex justify-center"></div>
        </div>
    </div>
</div>

<!-- Modale pour les produits d'une impression -->
<div id="impressionProduitsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 transform transition-all duration-200 scale-95 my-8">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-bold text-gray-900">Détails des produits imprimés</h3>
            </div>
            <button onclick="hideImpressionProduitsModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="p-6 max-h-[70vh] overflow-y-auto" id="impressionProduitsContent">
            <div class="flex justify-center">
                <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Div caché pour impression de reçu -->
<div id="ticket-addition" style="display:none;"></div>

<script>
    let formToSubmit = null;

    // Attendre que le DOM soit chargé
    document.addEventListener('DOMContentLoaded', function() {
        // Ajouter des événements aux boutons d'annulation
        const annulerButtons = document.querySelectorAll('.annuler-btn');
        annulerButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.stopPropagation(); // Empêche la propagation vers la ligne
                
                const form = this.closest('form');
                const tableNom = this.getAttribute('data-table');
                const montant = this.getAttribute('data-montant');
                
                showConfirmModal(form, tableNom, montant);
            });
        });
        
        // Gérer les clics sur les lignes du tableau
        const panierRows = document.querySelectorAll('.panier-row');
        panierRows.forEach(function(row) {
            row.addEventListener('click', function(e) {
                // Vérifier si le clic vient d'un bouton ou d'un formulaire
                if (e.target.closest('.annuler-form') || e.target.closest('button')) {
                    return;
                }
                
                const url = this.getAttribute('data-url');
                if (url && url !== '') {
                    window.location = url;
                }
            });
        });
        
        // Boutons pour voir les détails
        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                const panierId = btn.getAttribute('data-panier-id');
                showPanierDetails(panierId);
            });
        });
        
        // Boutons pour imprimer un reçu
        document.querySelectorAll('.print-receipt-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                const panierId = btn.getAttribute('data-panier-id');
                printReceipt(panierId);
            });
        });
        
        // Filtrer l'historique des impressions
        document.getElementById('filtrerImpressions').addEventListener('click', function() {
            const dateDebut = document.getElementById('date_debut_impressions').value;
            const dateFin = document.getElementById('date_fin_impressions').value;
            
            // Charger l'historique filtré via AJAX
            loadHistoriqueImpressions(dateDebut, dateFin);
        });
    });

    function showConfirmModal(form, tableNom, montant) {
        formToSubmit = form;
        document.getElementById('tableInfo').textContent = tableNom;
        document.getElementById('montantInfo').textContent = 'Montant: ' + montant;
        document.getElementById('confirmModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Animation d'entrée
        setTimeout(() => {
            document.querySelector('#confirmModal > div').style.transform = 'scale(1)';
        }, 10);
    }

    function hideConfirmModal() {
        // Animation de sortie
        document.querySelector('#confirmModal > div').style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            document.getElementById('confirmModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            formToSubmit = null;
        }, 150);
    }

    function confirmDelete() {
        if (formToSubmit) {
            // Ajouter un indicateur de chargement
            const submitBtn = document.querySelector('#confirmModal button[onclick="confirmDelete()"]');
            const originalText = submitBtn.textContent;
            submitBtn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>Suppression...';
            submitBtn.disabled = true;
            
            formToSubmit.submit();
        }
    }
    
    function showPanierDetails(panierId) {
        const modal = document.getElementById('detailsModal');
        const contentDiv = document.getElementById('panierDetailsContent');
        
        // Vérifier que la modale existe
        if (!modal) {
            console.error("Erreur: élément #detailsModal introuvable dans le DOM");
            alert("Erreur lors de l'affichage des détails du panier: modale introuvable");
            return;
        }
        
        // Vérifier que le conteneur de contenu existe
        if (!contentDiv) {
            console.error("Erreur: élément #panierDetailsContent introuvable dans le DOM");
            alert("Erreur lors de l'affichage des détails du panier: conteneur de contenu introuvable");
            return;
        }
        
        // Afficher la modale avec le loader
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        contentDiv.innerHTML = `<div class="flex justify-center">
            <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>`;
        
        // Animation d'entrée
        setTimeout(() => {
            document.querySelector('#detailsModal > div').style.transform = 'scale(1)';
        }, 10);
        
        console.log('Chargement des détails du panier ID: ' + panierId);
        
        // Charger les détails via fetch
        const url = `/paniers/${panierId}/details`;
        console.log('Requête à l\'API:', url);
        
        fetch(url)
            .then(response => {
                console.log('Statut de la réponse:', response.status);
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Données du panier reçues:', data);
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Construire le contenu HTML avec les détails du panier
                let html = `<div class="space-y-6">`;
                
                // En-tête avec infos générales
                html += `<div class="flex flex-col md:flex-row justify-between items-start gap-4 border-b pb-4">
                    <div>
                        <h3 class="font-bold text-lg">Panier #${data.id}</h3>
                        <p class="text-gray-600">Créé le ${new Date(data.created_at).toLocaleDateString('fr-FR')} à ${new Date(data.created_at).toLocaleTimeString('fr-FR')}</p>
                        <span class="inline-flex mt-2 items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            ${data.status === 'en_cours' ? 'bg-green-100 text-green-800' : 
                              data.status === 'payé' ? 'bg-blue-100 text-blue-800' : 
                              data.status === 'validé' ? 'bg-blue-100 text-blue-800' : 
                              'bg-red-100 text-red-800'}">
                            <span class="w-2 h-2 
                                ${data.status === 'en_cours' ? 'bg-green-400' : 
                                  data.status === 'payé' || data.status === 'validé' ? 'bg-blue-400' : 
                                  'bg-red-400'} 
                                rounded-full mr-1"></span>
                            ${data.status === 'en_cours' ? 'En cours' : 
                              data.status === 'payé' ? 'Payé' : 
                              data.status === 'validé' ? 'Validé' : 
                              'Annulé'}
                        </span>
                    </div>
                    <div class="text-right">
                        <div class="text-gray-600">Point de vente</div>
                        <div class="font-semibold text-blue-600">${data.point_de_vente?.nom || 'N/A'}</div>
                    </div>
                </div>`;
                
                // Infos client, serveuse, table
                html += `<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-sm text-gray-600">Client</div>
                        <div class="font-semibold">${data.client?.nom || 'Aucun client'}</div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-sm text-gray-600">Serveuse</div>
                        <div class="font-semibold">${data.serveuse?.name || 'Non assigné'}</div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-sm text-gray-600">Table</div>
                        <div class="font-semibold">${data.table?.numero || 'Table ' + data.table_id}</div>
                    </div>
                </div>`;
                
                // Liste des produits
                html += `<div>
                    <h4 class="font-semibold mb-2">Produits</h4>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qté</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">P.U.</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">`;
                
                let total = 0;
                
                // Vérifier si les produits existent et ajouter chaque produit au tableau
                if (data.produits && data.produits.length > 0) {
                    data.produits.forEach(produit => {
                        const quantite = produit.pivot.quantite;
                        const prix = produit.prix_vente;
                        const sousTotal = quantite * prix;
                        total += sousTotal;
                        
                        html += `<tr>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <div class="font-medium">${produit.nom}</div>
                                <div class="text-xs text-gray-500">${produit.categorie?.nom || ''}</div>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-center">${quantite}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right">${prix.toLocaleString('fr-FR')} F</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-medium">${sousTotal.toLocaleString('fr-FR')} F</td>
                        </tr>`;
                    });
                } else {
                    html += `<tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">Aucun produit dans ce panier</td></tr>`;
                }
                
                // Ligne de total
                html += `</tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-right font-medium">Total</td>
                            <td class="px-3 py-2 text-right font-bold">${total.toLocaleString('fr-FR')} F</td>
                        </tr>
                    </tfoot>
                    </table>
                </div>`;
                
                // Informations de paiement si payé ou validé
                if ((data.status === 'payé' || data.status === 'validé') && data.paiements && data.paiements.length > 0) {
                    html += `<div class="mt-4">
                        <h4 class="font-semibold mb-2">Informations de paiement</h4>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <div class="text-sm text-gray-600">Mode de paiement</div>
                                    <div class="font-semibold">${data.paiements[0].mode_paiement}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600">Montant reçu</div>
                                    <div class="font-semibold">${parseInt(data.paiements[0].montant_recu).toLocaleString('fr-FR')} F</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600">Monnaie rendue</div>
                                    <div class="font-semibold">${parseInt(data.paiements[0].monnaie).toLocaleString('fr-FR')} F</div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }
                
                // Informations de commande (si disponibles)
                if (data.commande) {
                    html += `<div class="mt-4">
                        <h4 class="font-semibold mb-2">Informations de commande</h4>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-600">Mode de paiement</div>
                                    <div class="font-semibold">${data.commande.mode_paiement || 'Non spécifié'}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600">État de la commande</div>
                                    <div class="font-semibold">${data.commande.statut || 'Non spécifié'}</div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }
                
                // Historique des impressions (si disponible)
                if (data.impressions && data.impressions.length > 0) {
                    html += `<div class="mt-4">
                        <h4 class="font-semibold mb-2">Historique des impressions</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Produits</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">`;
                    
                    data.impressions.forEach(impression => {
                        const date = new Date(impression.printed_at);
                        html += `<tr>
                            <td class="px-3 py-2 whitespace-nowrap">
                                ${date.toLocaleDateString('fr-FR')} ${date.toLocaleTimeString('fr-FR')}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                ${impression.user ? impression.user.name : 'Utilisateur inconnu'}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                ${parseFloat(impression.total).toLocaleString('fr-FR')} F
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" 
                                    class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-2 py-1 rounded text-xs"
                                    onclick="showImpressionDetails(${JSON.stringify(impression.produits).replace(/"/g, '&quot;')})">
                                    Voir les produits
                                </button>
                            </td>
                        </tr>`;
                    });
                    
                    html += `</tbody>
                            </table>
                        </div>
                    </div>`;
                }
                
                html += `</div>`;
                contentDiv.innerHTML = html;
            })
            .catch(error => {
                console.error("Erreur lors du chargement des détails du panier:", error);
                contentDiv.innerHTML = `<div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Erreur lors du chargement</h3>
                    <p class="mt-1 text-gray-500">Impossible de charger les détails du panier: ${error.message || 'Erreur inconnue'}</p>
                    <div class="mt-1 text-gray-500 text-sm">ID du panier: ${panierId}</div>
                    <div class="mt-1 text-gray-500 text-sm">URL: /paniers/${panierId}/details</div>
                    <div class="mt-4">
                        <button onclick="hideDetailsModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                            Fermer
                        </button>
                    </div>
                </div>`;
            });
    }
    
    function hideDetailsModal() {
        const modal = document.getElementById('detailsModal');
        // Animation de sortie
        document.querySelector('#detailsModal > div').style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 150);
    }
    
    // Fonction pour afficher les détails des produits d'une impression
    function showImpressionDetails(produits) {
        // Création d'une modale pour afficher les produits
        const modal = document.createElement('div');
        modal.id = 'impressionDetailsModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.onclick = function(e) {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        };
        
        let html = `
            <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Détails des produits imprimés</h3>
                        <button onclick="document.getElementById('impressionDetailsModal').remove()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qté</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">P.U.</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">`;
        
        let total = 0;
        produits.forEach(produit => {
            const sousTotal = produit.qte * parseFloat(produit.prix);
            total += sousTotal;
            
            html += `<tr>
                <td class="px-3 py-2 whitespace-nowrap">
                    <div class="font-medium">${produit.nom}</div>
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-center">${produit.qte}</td>
                <td class="px-3 py-2 whitespace-nowrap text-right">${parseFloat(produit.prix).toLocaleString('fr-FR')} F</td>
                <td class="px-3 py-2 whitespace-nowrap text-right font-medium">${sousTotal.toLocaleString('fr-FR')} F</td>
            </tr>`;
        });
        
        html += `</tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-right font-medium">Total</td>
                                <td class="px-3 py-2 text-right font-bold">${total.toLocaleString('fr-FR')} F</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;
        
        modal.innerHTML = html;
        document.body.appendChild(modal);
    }
    
    function printReceipt(panierId) {
        fetch(`/paniers/${panierId}/print-receipt`)
            .then(response => response.json())
            .then(data => {
                // Créer le contenu HTML du ticket
                let html = `<div style='width:62mm;margin:0 auto;padding-left:4mm;padding-right:2.5mm;padding-top:0;padding-bottom:0;font-family:monospace;'>`;
                
                // Type de reçu
                html += `<div style='text-align:center;font-size:13px;font-weight:bold;color:#222;margin-bottom:2px;'>REÇU DE PAIEMENT</div>`;
                
                // Logo si disponible
                if (data.entreprise && data.entreprise.logo) {
                    html += `<div style='text-align:center;'><img src='${window.location.origin}/storage/${data.entreprise.logo}' style='max-width:40px;max-height:40px;margin-bottom:2px;display:block;margin-left:auto;margin-right:auto;'/></div>`;
                }
                
                // Nom + infos entreprise
                if (data.entreprise) {
                    html += `<div style='text-align:center;font-weight:bold;font-size:15px;'>${data.entreprise.nom || ''}</div>`;
                    if (data.entreprise.numero_entreprise) html += `<div style='text-align:center;font-size:11px;'>N° Entreprise : ${data.entreprise.numero_entreprise}</div>`;
                    if (data.entreprise.email) html += `<div style='text-align:center;font-size:11px;'>${data.entreprise.email}</div>`;
                    if (data.entreprise.telephone) html += `<div style='text-align:center;font-size:11px;'>${data.entreprise.telephone}</div>`;
                    if (data.entreprise.adresse) html += `<div style='text-align:center;font-size:11px;'>${data.entreprise.adresse}</div>`;
                }
                
                html += `<div style='border-top:1px dashed #222;margin:6px 0;'></div>`;
                
                // Infos client/serveuse/table/panier
                html += `<div style='font-size:11px;'>Client : <b>${data.client?.nom || '-'}</b></div>`;
                html += `<div style='font-size:11px;'>Servie par : <b>${data.serveuse?.name || '-'}</b></div>`;
                html += `<div style='font-size:11px;'>Table : <b>${data.table?.numero || 'Table ' + data.table_id}</b> | Panier n° <b>${data.id || '-'}</b></div>`;
                
                if (data.paiements && data.paiements.length > 0) {
                    html += `<div style='font-size:11px;'>Mode de paiement : <b>${data.paiements[0].mode_paiement}</b></div>`;
                }
                
                html += `<div style='border-top:1px dashed #222;margin:6px 0;'></div>`;
                
                // Tableau produits
                html += `<table style='width:100%;font-size:11px;margin:0 auto;'><thead><tr><th style='text-align:left;'>Produit</th><th>Qté</th><th style='text-align:right;'>Prix</th><th style='text-align:right;'>Total</th></tr></thead><tbody>`;
                
                let total = 0;
                
                if (data.produits && data.produits.length > 0) {
                    data.produits.forEach(item => {
                        const lineTotal = item.pivot.quantite * item.prix_vente;
                        total += lineTotal;
                        html += `<tr><td style='word-break:break-all;'>${item.nom}</td><td style='text-align:center;'>${item.pivot.quantite}</td><td style='text-align:right;'>${Math.round(item.prix_vente).toLocaleString('fr-FR')} F</td><td style='text-align:right;'>${lineTotal.toLocaleString('fr-FR')} F</td></tr>`;
                    });
                }
                
                html += `</tbody></table>`;
                html += `<div style='border-top:1px dashed #222;margin:6px 0;'></div>`;
                html += `<div style='text-align:right;font-size:14px;font-weight:bold;'>TOTAL : ${total.toLocaleString('fr-FR')} F</div>`;
                
                if (data.paiements && data.paiements.length > 0) {
                    html += `<div style='text-align:right;font-size:12px;margin-top:4px;'>Montant reçu : ${parseInt(data.paiements[0].montant_recu).toLocaleString('fr-FR')} F</div>`;
                    html += `<div style='text-align:right;font-size:12px;'>Monnaie : ${parseInt(data.paiements[0].monnaie).toLocaleString('fr-FR')} F</div>`;
                }
                
                // Ajouter les informations de mode de paiement de la commande si disponible
                if (data.commande && data.commande.mode_paiement) {
                    if (!data.paiements || data.paiements.length === 0) {
                        html += `<div style='text-align:right;font-size:12px;margin-top:4px;'>Mode de paiement : <b>${data.commande.mode_paiement}</b></div>`;
                    }
                }
                
                html += `<div style='text-align:center;font-size:11px;margin-top:10px;'>Merci pour votre visite !</div>`;
                
                let dateStr = new Date(data.updated_at || data.created_at).toLocaleDateString('fr-FR');
                let heureStr = new Date(data.updated_at || data.created_at).toLocaleTimeString('fr-FR');
                html += `<div style='text-align:center;font-size:10px;margin-top:8px;'>Généré par Ayanna &copy; | ${dateStr} ${heureStr}</div>`;
                
                html += `</div>`;
                
                // Afficher et imprimer le reçu
                document.getElementById('ticket-addition').innerHTML = html;
                const printWindow = window.open('', '', 'width=900,height=800');
                printWindow.document.write('<html><head><title>Reçu</title>');
                printWindow.document.write('<style>body{margin:0;padding:0;}@media print{body{width:70mm!important;margin:0!important;padding:0!important;}}</style>');
                printWindow.document.write('</head><body >');
                printWindow.document.write(html);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => { printWindow.print(); printWindow.close(); }, 800);
            })
            .catch(error => {
                alert('Erreur lors de l\'impression du reçu');
                console.error("Erreur lors de l'impression du reçu:", error);
            });
    }

    // Fermer la modale avec Échap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideDetailsModal();
            hideHistoriqueImpressionsModal();
            hideImpressionProduitsModal();
            
            // Fermer aussi la modale d'impression des produits si elle existe
            const impressionDetailsModal = document.getElementById('impressionDetailsModal');
            if (impressionDetailsModal) {
                impressionDetailsModal.remove();
            }
        }
    });
    
    // Gestion de la modale d'historique des impressions
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les dates par défaut
        const today = new Date();
        document.getElementById('date_debut_impressions').valueAsDate = new Date(today.getFullYear(), today.getMonth(), 1); // Premier jour du mois
        document.getElementById('date_fin_impressions').valueAsDate = today;
        
        // Ajouter l'écouteur d'événements au bouton d'historique
        document.getElementById('btn-historique-impressions').addEventListener('click', function(e) {
            e.preventDefault();
            showHistoriqueImpressionsModal();
        });
        
        // Écouteur pour le bouton de filtrage
        document.getElementById('filtrerImpressions').addEventListener('click', function() {
            loadHistoriqueImpressions(1);
        });
    });
    
    function showHistoriqueImpressionsModal() {
        const modal = document.getElementById('historiqueImpressionsModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Animation d'entrée
        setTimeout(() => {
            document.querySelector('#historiqueImpressionsModal > div').style.transform = 'scale(1)';
        }, 10);
        
        // Charger les données
        loadHistoriqueImpressions(1);
    }
    
    function hideHistoriqueImpressionsModal() {
        const modal = document.getElementById('historiqueImpressionsModal');
        
        // Animation de sortie
        document.querySelector('#historiqueImpressionsModal > div').style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 150);
    }
    
    function loadHistoriqueImpressions(page = 1) {
        const contentDiv = document.getElementById('historiqueImpressionsContent');
        const paginationDiv = document.getElementById('historiquePagination');
        
        // Afficher le loader
        contentDiv.innerHTML = `<div class="flex justify-center">
            <svg class="animate-spin h-10 w-10 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>`;
        paginationDiv.innerHTML = '';
        
        // Récupérer les filtres
        const dateDebut = document.getElementById('date_debut_impressions').value;
        const dateFin = document.getElementById('date_fin_impressions').value;
        
        // Construire l'URL avec les paramètres
        let url = `/paniers/api/historique-impressions?page=${page}`;
        if (dateDebut) url += `&date_debut=${dateDebut}`;
        if (dateFin) url += `&date_fin=${dateFin}`;
        
        // Appel à l'API
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Données historique impressions reçues:', data);
                
                if (!data.success) {
                    throw new Error(data.error || 'Erreur lors du chargement des données');
                }
                
                // Vérifier si des impressions existent
                if (!data.impressions || data.impressions.length === 0) {
                    contentDiv.innerHTML = `<div class="text-center py-12 bg-gray-50 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-xl font-medium text-gray-600 mb-2">Aucune impression trouvée</h3>
                        <p class="text-gray-500">Essayez de modifier vos critères de recherche</p>
                    </div>`;
                    return;
                }
                
                // Construire le tableau des impressions
                let html = `<div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-purple-100 text-gray-700">
                            <tr>
                                <th class="p-3 text-left">Date</th>
                                <th class="p-3 text-left">Utilisateur</th>
                                <th class="p-3 text-left">Panier</th>
                                <th class="p-3 text-left">Point de vente</th>
                                <th class="p-3 text-left">Table</th>
                                <th class="p-3 text-right">Montant</th>
                                <th class="p-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                // Ajouter chaque impression au tableau
                data.impressions.forEach(impression => {
                    html += `<tr class="hover:bg-gray-50 ${impression.panier_existe ? '' : 'bg-red-50 border-l-4 border-red-400'}">
                        <td class="p-3">
                            <div class="font-medium">${impression.date_impression}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium">${impression.utilisateur ? impression.utilisateur.nom : 'Utilisateur inconnu'}</div>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center">
                                <span class="font-medium">#${impression.panier_id}</span>
                                ${!impression.panier_existe ? '<span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><span class="w-2 h-2 bg-red-400 rounded-full mr-1"></span>Supprimé</span>' : ''}
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium text-blue-600">${impression.point_de_vente ? impression.point_de_vente.nom : 'N/A'}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium">${impression.table ? impression.table.numero || 'Table ' + impression.table.id : 'N/A'}</div>
                        </td>
                        <td class="p-3 text-right font-medium">
                            ${parseInt(impression.montant).toLocaleString('fr-FR')} F
                        </td>
                        <td class="p-3 flex justify-center space-x-2">
                            <button class="bg-blue-600 text-white rounded-full p-2 hover:bg-blue-700 view-produits-btn" 
                                title="Voir produits" 
                                data-produits='${JSON.stringify(impression.produits)}'>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg                            </button>
                        </td>
                    </tr>`;
                });
                
                html += `</tbody>
                    </table>
                </div>`;
                
                contentDiv.innerHTML = html;
                
                // Ajouter la pagination
                if (data.pagination && data.pagination.last_page > 1) {
                    let paginationHtml = '<div class="flex items-center justify-between px-4 py-3 border-t border-gray-200 sm:px-6">';
                    
                    // Bouton précédent
                    paginationHtml += `<div class="flex-1 flex justify-between sm:hidden">
                        <button ${page > 1 ? `onclick="loadHistoriqueImpressions(${page - 1})"` : 'disabled'} 
                        class="${page > 1 ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-300 cursor-not-allowed'} text-white px-4 py-2 rounded-lg transition">
                            Précédent
                        </button>
                        <button ${page < data.pagination.last_page ? `onclick="loadHistoriqueImpressions(${page + 1})"` : 'disabled'} 
                        class="${page < data.pagination.last_page ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-300 cursor-not-allowed'} text-white px-4 py-2 rounded-lg transition">
                            Suivant
                        </button>
                    </div>`;
                    
                    // Pagination complète
                    paginationHtml += `<div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center">
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">`;
                    
                    // Bouton précédent
                    paginationHtml += `<button ${page > 1 ? `onclick="loadHistoriqueImpressions(${page - 1})"` : 'disabled'} 
                    class="${page > 1 ? 'hover:bg-gray-50' : 'bg-gray-100 cursor-not-allowed'} relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500">
                        <span class="sr-only">Précédent</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>`;
                    
                    // Numéros de page
                    const maxVisiblePages = 5;
                    let startPage = Math.max(1, page - Math.floor(maxVisiblePages / 2));
                    let endPage = Math.min(data.pagination.last_page, startPage + maxVisiblePages - 1);
                    
                    if (startPage > 1) {
                        paginationHtml += `<button onclick="loadHistoriqueImpressions(1)" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</button>`;
                        if (startPage > 2) {
                            paginationHtml += `<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>`;
                        }
                    }
                    
                    for (let i = startPage; i <= endPage; i++) {
                        paginationHtml += `<button onclick="loadHistoriqueImpressions(${i})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 ${page === i ? 'bg-purple-50 text-purple-600 font-bold' : 'bg-white text-gray-700 hover:bg-gray-50'} text-sm font-medium">${i}</button>`;
                    }
                    
                    if (endPage < data.pagination.last_page) {
                        if (endPage < data.pagination.last_page - 1) {
                            paginationHtml += `<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>`;
                        }
                        paginationHtml += `<button onclick="loadHistoriqueImpressions(${data.pagination.last_page})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">${data.pagination.last_page}</button>`;
                    }
                    
                    // Bouton suivant
                    paginationHtml += `<button ${page < data.pagination.last_page ? `onclick="loadHistoriqueImpressions(${page + 1})"` : 'disabled'} 
                    class="${page < data.pagination.last_page ? 'hover:bg-gray-50' : 'bg-gray-100 cursor-not-allowed'} relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500">
                        <span class="sr-only">Suivant</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>`;
                    
                    paginationHtml += `</nav></div></div>`;
                    paginationDiv.innerHTML = paginationHtml;
                }
                
                // Ajouter les écouteurs d'événements pour les boutons de produits
                document.querySelectorAll('.view-produits-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const produits = JSON.parse(this.getAttribute('data-produits'));
                        showImpressionProduits(produits);
                    });
                });
            })
            .catch(error => {
                console.error("Erreur lors du chargement de l'historique des impressions:", error);
                contentDiv.innerHTML = `<div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Erreur lors du chargement</h3>
                    <p class="mt-1 text-gray-500">Impossible de charger l'historique des impressions: ${error.message || 'Erreur inconnue'}</p>
                </div>`;
            });
    }
    
    function showImpressionProduits(produits) {
        const modal = document.getElementById('impressionProduitsModal');
        const contentDiv = document.getElementById('impressionProduitsContent');
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Animation d'entrée
        setTimeout(() => {
            document.querySelector('#impressionProduitsModal > div').style.transform = 'scale(1)';
        }, 10);
        
        // Afficher le loader
        contentDiv.innerHTML = `<div class="flex justify-center">
            <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>`;
        
        // Construire le tableau des produits
        let html = `<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-100 text-gray-700">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qté</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">P.U.</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">`;
        
        let total = 0;
        
        if (produits && produits.length > 0) {
            produits.forEach(produit => {
                const prix = parseFloat(produit.prix);
                const quantite = parseInt(produit.qte);
                const sousTotal = prix * quantite;
                total += sousTotal;
                
                html += `<tr>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <div class="font-medium">${produit.nom}</div>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-center">${quantite}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-right">${prix.toLocaleString('fr-FR')} F</td>
                    <td class="px-3 py-2 whitespace-nowrap text-right font-medium">${sousTotal.toLocaleString('fr-FR')} F</td>
                </tr>`;
            });
        } else {
            html += `<tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">Aucun produit dans cette impression</td></tr>`;
        }
        
        // Ligne de total
        html += `</tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-3 py-2 text-right font-medium">Total</td>
                    <td class="px-3 py-2 text-right font-bold">${total.toLocaleString('fr-FR')} F</td>
                </tr>
            </tfoot>
        </table>`;
        
        contentDiv.innerHTML = html;
    }
    
    function hideImpressionProduitsModal() {
        const modal = document.getElementById('impressionProduitsModal');
        
        // Animation de sortie
        document.querySelector('#impressionProduitsModal > div').style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 150);
    }
</script>
@endsection
