@extends('layouts.appvente')
@section('content')

<div class="max-w-7xl mx-auto px-6 py-3">
    <!-- Messages de statut -->
    @if(session('success'))
        <div class="mb-3 p-3 bg-green-100 border border-green-300 text-green-700 rounded-lg text-center font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-3 p-3 bg-red-100 border border-red-300 text-red-700 rounded-lg text-center font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- En-tête redesigné avec contrôles centraux -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-4 border border-gray-100">
        <!-- Ligne principale : Titre - Contrôles - Statistiques -->
        <div class="flex items-center justify-between gap-6">
            <!-- Titre à gauche -->
            <div class="flex-shrink-0">
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <svg class="w-7 h-7 text-orange-500 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Créances
                </h1>
            </div>
            
            <!-- Contrôles centraux -->
            <div class="flex-1 max-w-2xl">
                <div class="flex gap-4 items-center justify-center">
                    <!-- Filtre par période avec style moderne -->
                    <form method="GET" class="flex items-center gap-3">
                        <div class="flex items-center gap-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg px-4 py-2 border border-blue-200">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <select name="filtre" onchange="this.form.submit()" 
                                    class="bg-transparent border-none text-sm font-medium text-blue-700 focus:ring-0 focus:outline-none cursor-pointer">
                                <option value="jour" {{ $filtre === 'jour' ? 'selected' : '' }}>Aujourd'hui</option>
                                <option value="toutes" {{ $filtre === 'toutes' ? 'selected' : '' }}>Toutes</option>
                            </select>
                        </div>
                    </form>
                    
                    <!-- Barre de recherche moderne -->
                    <div class="flex-1 max-w-md relative">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" id="search-creance" 
                                   placeholder="Rechercher client, serveuse, table..." 
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors" 
                                   oninput="filtrerCreances()">
                        </div>
                    </div>
                    
                    <!-- Bouton Export Liste -->
                    <button onclick="exporterListe()" 
                            class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium rounded-lg text-sm hover:from-green-700 hover:to-emerald-700 transition-all shadow-sm"
                            title="Exporter la liste actuelle en PDF">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Liste
                    </button>
                </div>
            </div>
            
            <!-- Statistiques à droite -->
            <div class="flex-shrink-0">
                <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-xl px-6 py-4 border border-orange-200 shadow-sm">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600 total-creances-montant">
                            @php
                                $totalRestant = $creances->sum(function($commande) {
                                    if ($commande->panier && $commande->panier->produits) {
                                        $montantTotal = $commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente);
                                        $montantPaye = $commande->paiements->sum('montant');
                                        return max(0, $montantTotal - $montantPaye);
                                    }
                                    return 0;
                                });
                            @endphp
                            {{ number_format($totalRestant, 0, ',', ' ') }} F
                        </div>
                        <div class="text-xs text-orange-700 font-medium total-creances-nombre">{{ $creances->count() }} créances</div>
                        <div class="text-xs text-gray-500 mt-1">À encaisser</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau compact des créances -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
        @if($creances->isNotEmpty())
            <div class="overflow-x-auto">
                <table id="table-creances" class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Table</th>        
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Client</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Serveuse</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Date</th>
                            <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Total</th>
                            <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Restant</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Statut</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="body-creances" class="divide-y divide-gray-200">
                        @foreach($creances as $commande)
                            @php
                                $montantTotal = $commande->panier && $commande->panier->produits ? 
                                    $commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente) : 0;
                                $montantPaye = $commande->paiements ? $commande->paiements->sum('montant') : 0;
                                $montantRestant = max(0, $montantTotal - $montantPaye);
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer text-sm" 
                                onclick="afficherDetails({{ $commande->id }})" 
                                data-client="{{ strtolower($commande->panier->client->nom ?? '') }}" 
                                data-serveuse="{{ strtolower($commande->panier->serveuse->name ?? '') }}" 
                                data-table="{{ $commande->panier->tableResto->numero ?? '' }}"
                                data-heure="{{ \Carbon\Carbon::parse($commande->created_at)->format('H:i') }}"
                                data-date="{{ \Carbon\Carbon::parse($commande->created_at)->format('d/m/Y') }}"
                                data-montant-restant="{{ $montantRestant }}">
                                
                                <!-- Table -->
                                <td class="px-4 py-2">
                                    <div class="bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center font-bold text-xs">
                                        {{ $commande->panier->tableResto->numero ?? 'N/A' }}
                                    </div>
                                </td>
                                
                                <!-- Client -->
                                <td class="px-4 py-2">
                                    <div class="font-medium text-gray-900">{{ $commande->panier->client->nom ?? 'N/A' }}</div>
                                </td>
                                
                                <!-- Serveuse -->
                                <td class="px-4 py-2">
                                    <div class="text-gray-700">{{ $commande->panier->serveuse->name ?? 'N/A' }}</div>
                                </td>
                                
                                <!-- Date/Heure -->
                                <td class="px-4 py-2">
                                    <div class="text-gray-900">{{ \Carbon\Carbon::parse($commande->created_at)->format('d/m') }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($commande->created_at)->format('H:i') }}</div>
                                </td>
                                
                                <!-- Montant total -->
                                <td class="px-4 py-2 text-right">
                                    <div class="font-semibold text-green-600">
                                        {{ number_format($commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente), 0, ',', ' ') }} F
                                    </div>
                                </td>
                                
                                <!-- Montant restant -->
                                <td class="px-4 py-2 text-right">
                                    @php
                                        $montantTotal = $commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente);
                                        $montantPaye = $commande->paiements->sum('montant');
                                        $montantRestant = $montantTotal - $montantPaye;
                                    @endphp
                                    
                                    @if($montantRestant <= 0)
                                        <div class="font-semibold text-green-600">
                                            <span class="inline-flex items-center gap-1 text-xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Soldé
                                            </span>
                                        </div>
                                    @else
                                        <div class="font-semibold text-orange-600">
                                            {{ number_format($montantRestant, 0, ',', ' ') }} F
                                        </div>
                                        @if($montantPaye > 0)
                                            <div class="text-xs text-gray-500">
                                                Payé: {{ number_format($montantPaye, 0, ',', ' ') }} F
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                
                                <!-- Statut -->
                                <td class="px-4 py-2 text-center">
                                    @if($commande->mode_paiement === 'compte_client' && $commande->statut === 'payé')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-700 font-medium text-xs">
                                            Payé
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-orange-100 text-orange-700 font-medium text-xs">
                                            En attente
                                        </span>
                                    @endif
                                </td>
                                
                                <!-- Actions -->
                                <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                                    <div class="flex justify-center gap-2">
                                        @if($commande->mode_paiement === 'compte_client' && $commande->statut !== 'payé')
                                            <!-- Bouton Payer -->
                                            <button onclick="ouvrirModalePaiement(this)"
                                                    data-commande-id="{{ $commande->id }}"
                                                    data-client-nom="{{ $commande->panier->client->nom ?? 'N/A' }}"
                                                    data-montant-total="{{ $commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente) }}"
                                                    data-montant-restant="{{ $commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente) - $commande->paiements->sum('montant') }}"
                                                    class="inline-flex items-center px-3 py-2 bg-green-600 text-white font-medium rounded-lg text-sm hover:bg-green-700 transition-colors shadow-sm"
                                                    title="Encaisser le paiement">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.268-.268-1.268-.732 0-.464.543-.732 1.268-.732.725 0 1.268.268 1.268.732"/>
                                                </svg>
                                                Payer
                                            </button>
                                            
                                            <!-- Bouton Historique -->
                                            <a href="{{ route('creances.historique', $commande->id) }}" target="_blank"
                                               class="inline-flex items-center px-3 py-2 bg-blue-600 text-white font-medium rounded-lg text-sm hover:bg-blue-700 transition-colors shadow-sm"
                                               title="Voir l'historique des paiements">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Historique
                                            </a>
                                            
                                            <!-- Bouton Export PDF -->
                                            <a href="{{ route('creances.imprimer', $commande->id) }}" target="_blank"
                                               class="inline-flex items-center px-3 py-2 bg-gray-600 text-white font-medium rounded-lg text-sm hover:bg-gray-700 transition-colors shadow-sm"
                                               title="Télécharger le reçu PDF">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                PDF
                                            </a>
                                        @else
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 font-medium text-sm border border-green-200">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Payé
                                                </span>
                                                
                                                <!-- Bouton Historique -->
                                                <a href="{{ route('creances.historique', $commande->id) }}" target="_blank"
                                                   class="inline-flex items-center px-3 py-2 bg-blue-600 text-white font-medium rounded-lg text-sm hover:bg-blue-700 transition-colors shadow-sm"
                                                   title="Voir l'historique des paiements">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Historique
                                                </a>
                                                
                                                <!-- Bouton Export PDF -->
                                                <a href="{{ route('creances.imprimer', $commande->id) }}" target="_blank"
                                                   class="inline-flex items-center px-3 py-2 bg-gray-600 text-white font-medium rounded-lg text-sm hover:bg-gray-700 transition-colors shadow-sm"
                                                   title="Télécharger le reçu PDF">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                    </svg>
                                                    PDF
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                <div id="pagination-creances" class="flex justify-center gap-2"></div>
            </div>
        @else
            <div class="p-8 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-full mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune créance</h3>
                <p class="text-gray-500 text-sm">
                    @if($filtre === 'jour')
                        Aucune créance pour aujourd'hui.
                    @else
                        Aucune créance enregistrée.
                    @endif
                </p>
            </div>
        @endif
    </div>

    <!-- Panneau de détails (sidebar) -->
    <div id="details-creance" class="fixed inset-y-0 right-0 w-96 bg-white shadow-2xl border-l border-gray-200 transform translate-x-full transition-transform duration-300 ease-in-out z-50 hidden">
        <div class="flex flex-col h-full">
            <!-- Header du panneau -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-blue-50">
                <h2 class="text-xl font-bold text-blue-700">Détail du panier</h2>
                <button onclick="fermerDetails()" class="p-2 hover:bg-blue-100 rounded-lg transition-colors" title="Fermer">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Contenu du panneau -->
            <div class="flex-1 overflow-y-auto p-6">
                <div id="contenu-details"></div>
            </div>
        </div>
    </div>

    <!-- Overlay pour fermer le panneau -->
    <div id="overlay-details" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="fermerDetails()"></div>

    <!-- Modale de paiement -->
    <div id="modalePaiement" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 transform transition-all duration-200 scale-95 max-h-[90vh] overflow-y-auto">
            <!-- Header de la modale -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-t-2xl">
                <div class="flex items-center">
                    <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 mr-3 filter brightness-0 invert">
                    <h3 class="text-lg font-bold">Enregistrer un paiement</h3>
                </div>
                <button onclick="fermerModalePaiement()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Contenu de la modale -->
            <form id="formPaiement" class="p-6">
                <input type="hidden" id="commandeId" name="commande_id">
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Colonne gauche : Informations de la créance -->
                    <div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200 h-fit">
                            <h4 class="font-bold text-blue-700 mb-3">Détails de la créance</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Client :</span>
                                    <span class="font-medium" id="clientNom"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Montant total :</span>
                                    <span class="font-bold text-green-600" id="montantTotal"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Montant restant :</span>
                                    <span class="font-bold text-orange-600" id="montantRestant"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Message d'info -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-4">
                            <p class="text-sm text-yellow-800">
                                💡 Si le montant correspond au total, la créance sera automatiquement soldée.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Colonne droite : Champs du formulaire -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Montant reçu *</label>
                            <input type="number" id="montantRecu" name="montant" step="0.01" min="0.01" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                   placeholder="Entrez le montant reçu">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Mode de paiement *</label>
                            <select id="modePaiement" name="mode" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="especes">💵 Espèces</option>
                                <option value="carte">💳 Carte bancaire</option>
                                <option value="cheque">📝 Chèque</option>
                                <option value="virement">🏦 Virement</option>
                                <option value="mobile">📱 Paiement mobile</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Notes (optionnel)</label>
                            <textarea id="notes" name="notes" rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Commentaires sur le paiement..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Footer de la modale -->
            <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                <button onclick="fermerModalePaiement()" class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Annuler
                </button>
                <button onclick="enregistrerPaiement()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-colors shadow-lg">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer le paiement
                </button>
            </div>
        </div>
    </div>

    <!-- Bouton retour compact -->
    <div class="mt-4 flex justify-center">
        <a href="{{ url()->previous() }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded hover:bg-gray-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Retour
        </a>
    </div>
</div>
    <script>
        const creances = {!! json_encode($creances) !!};
        let page = 1;
        const lignesParPage = 10;

        // Fonctions pour la modale de paiement
        function ouvrirModalePaiement(button) {
            const commandeId = button.getAttribute('data-commande-id');
            const clientNom = button.getAttribute('data-client-nom');
            const montantTotal = parseFloat(button.getAttribute('data-montant-total'));
            const montantRestant = parseFloat(button.getAttribute('data-montant-restant'));
            
            document.getElementById('commandeId').value = commandeId;
            document.getElementById('clientNom').textContent = clientNom;
            document.getElementById('montantTotal').textContent = montantTotal.toLocaleString() + ' F';
            document.getElementById('montantRestant').textContent = montantRestant.toLocaleString() + ' F';
            document.getElementById('montantRecu').value = '';
            document.getElementById('montantRecu').max = montantRestant;
            
            document.getElementById('modalePaiement').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            setTimeout(() => {
                document.querySelector('#modalePaiement > div').style.transform = 'scale(1)';
            }, 10);
        }

        function fermerModalePaiement() {
            document.querySelector('#modalePaiement > div').style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                document.getElementById('modalePaiement').classList.add('hidden');
                document.body.style.overflow = '';
                
                // Reset du formulaire
                document.getElementById('formPaiement').reset();
            }, 150);
        }

        async function enregistrerPaiement() {
            const form = document.getElementById('formPaiement');
            const formData = new FormData(form);
            const commandeId = document.getElementById('commandeId').value;
            
            // Validation
            if (!formData.get('montant') || parseFloat(formData.get('montant')) <= 0) {
                alert('Veuillez entrer un montant valide');
                return;
            }
            
            const submitBtn = document.querySelector('#modalePaiement button[onclick="enregistrerPaiement()"]');
            const originalText = submitBtn ? submitBtn.textContent : 'Enregistrer le paiement';
            
            try {
                if (submitBtn) {
                    submitBtn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>Enregistrement...';
                    submitBtn.disabled = true;
                }
                
                const response = await fetch(`/creances/${commandeId}/paiement`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        montant: formData.get('montant'),
                        mode: formData.get('mode'),
                        notes: formData.get('notes')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    fermerModalePaiement();
                    
                    // Afficher un message de succès
                    const successDiv = document.createElement('div');
                    successDiv.className = 'mb-6 p-4 bg-green-100 border border-green-300 text-green-700 rounded-xl shadow-sm text-center font-semibold';
                    successDiv.textContent = result.message;
                    document.querySelector('.max-w-7xl').insertBefore(successDiv, document.querySelector('.max-w-7xl').firstChild);
                    
                    // Recharger la page après 2 secondes
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    alert('Erreur lors de l\'enregistrement du paiement');
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                alert('Une erreur est survenue');
            } finally {
                if (submitBtn) {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            }
        }

        // Fonctions existantes pour les détails et pagination
        function afficherDetails(id) {
            const zone = document.getElementById('details-creance');
            const overlay = document.getElementById('overlay-details');
            const contenu = document.getElementById('contenu-details');
            const commande = creances.find(c => c.id === id);
            
            if (!commande || !commande.panier || !commande.panier.produits) return;
            
            // Informations de la commande
            let html = `
                <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-200">
                    <h3 class="font-bold text-blue-700 mb-3">Informations de la commande</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Table :</span>
                            <span class="font-medium">${commande.panier.table_resto?.numero || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Client :</span>
                            <span class="font-medium">${commande.panier.client?.nom || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Serveuse :</span>
                            <span class="font-medium">${commande.panier.serveuse?.name || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Heure :</span>
                            <span class="font-medium">${new Date(commande.created_at).toLocaleString('fr-FR')}</span>
                        </div>
                    </div>
                </div>
                
                <h3 class="font-bold text-gray-700 mb-4">Détail des produits</h3>
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left font-bold text-gray-700">Produit</th>
                                <th class="px-3 py-3 text-center font-bold text-gray-700">Qté</th>
                                <th class="px-3 py-3 text-right font-bold text-gray-700">Prix</th>
                                <th class="px-3 py-3 text-right font-bold text-gray-700">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
            `;
            
            let totalGeneral = 0;
            commande.panier.produits.forEach(prod => {
                const total = prod.pivot.quantite * prod.prix_vente;
                totalGeneral += total;
                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 font-medium text-gray-900">${prod.nom}</td>
                        <td class="px-3 py-3 text-center text-gray-700">${prod.pivot.quantite}</td>
                        <td class="px-3 py-3 text-right text-gray-700">${prod.prix_vente.toLocaleString()} F</td>
                        <td class="px-3 py-3 text-right font-bold text-green-600">${total.toLocaleString()} F</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                        <tfoot class="bg-green-50 border-t-2 border-green-200">
                            <tr>
                                <td colspan="3" class="px-3 py-4 font-bold text-green-700">Total commande :</td>
                                <td class="px-3 py-4 text-right font-bold text-green-700 text-lg">${totalGeneral.toLocaleString()} F</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
            
            contenu.innerHTML = html;
            zone.classList.remove('hidden', 'translate-x-full');
            overlay.classList.remove('hidden');
            
            // Empêcher le scroll du body
            document.body.style.overflow = 'hidden';
        }

        function fermerDetails() {
            const zone = document.getElementById('details-creance');
            const overlay = document.getElementById('overlay-details');
            
            zone.classList.add('translate-x-full');
            overlay.classList.add('hidden');
            
            // Réactiver le scroll du body
            document.body.style.overflow = '';
            
            setTimeout(() => {
                zone.classList.add('hidden');
            }, 300);
        }

        // Fonction pour exporter la liste des créances
        function exporterListe() {
            const search = document.getElementById('search-creance').value;
            const filtre = '{{ $filtre }}';
            
            // Collecter les IDs des créances visibles
            const rows = document.querySelectorAll('#body-creances tr');
            const creancesVisibles = [];
            
            rows.forEach(row => {
                if (row.getAttribute('data-visible') !== '0' && row.style.display !== 'none') {
                    // Extraire l'ID de la commande depuis l'attribut onclick
                    const onclickAttr = row.getAttribute('onclick');
                    if (onclickAttr) {
                        const match = onclickAttr.match(/afficherDetails\((\d+)\)/);
                        if (match) {
                            creancesVisibles.push(parseInt(match[1]));
                        }
                    }
                }
            });
            
            if (creancesVisibles.length === 0) {
                alert('Aucune créance à exporter');
                return;
            }
            
            // Construire l'URL d'export avec les paramètres
            const params = new URLSearchParams({
                filtre: filtre,
                search: search,
                ids: creancesVisibles.join(',')
            });
            
            // Ouvrir l'export dans un nouvel onglet
            window.open(`/creances/export-liste?${params.toString()}`, '_blank');
        }

        function filtrerCreances() {
            const search = document.getElementById('search-creance').value.toLowerCase();
            const rows = document.querySelectorAll('#body-creances tr');
            let totalRestantFiltre = 0;
            let nombreCreancesFiltre = 0;
            
            rows.forEach(row => {
                const client = row.getAttribute('data-client');
                const serveuse = row.getAttribute('data-serveuse');
                const table = row.getAttribute('data-table');
                const heure = row.getAttribute('data-heure');
                const date = row.getAttribute('data-date');
                
                if (client.includes(search) || serveuse.includes(search) || 
                    table.includes(search) || heure.includes(search) || 
                    date.includes(search)) {
                    row.setAttribute('data-visible', '1');
                    
                    // Récupérer le montant restant de cette ligne depuis l'attribut data
                    const montantRestant = parseFloat(row.getAttribute('data-montant-restant')) || 0;
                    totalRestantFiltre += montantRestant;
                    nombreCreancesFiltre++;
                } else {
                    row.setAttribute('data-visible', '0');
                }
            });
            
            // Mettre à jour les statistiques affichées
            const totalElement = document.querySelector('.total-creances-montant');
            const nombreElement = document.querySelector('.total-creances-nombre');
            
            if (totalElement) {
                totalElement.textContent = totalRestantFiltre.toLocaleString() + ' F';
            }
            if (nombreElement) {
                nombreElement.textContent = nombreCreancesFiltre + ' créances';
            }
            
            page = 1;
            paginerCreances();
        }

        function paginerCreances() {
            const rows = Array.from(document.querySelectorAll('#body-creances tr'));
            const visibles = rows.filter(row => row.getAttribute('data-visible') !== '0');
            
            // Masquer toutes les lignes
            rows.forEach(row => row.style.display = 'none');
            
            // Afficher les lignes de la page courante
            visibles.forEach((row, i) => {
                row.style.display = (i >= (page-1)*lignesParPage && i < page*lignesParPage) ? '' : 'none';
            });
            
            // Générer la pagination
            const nbPages = Math.ceil(visibles.length / lignesParPage);
            const pagDiv = document.getElementById('pagination-creances');
            pagDiv.innerHTML = '';
            
            if (nbPages > 1) {
                // Bouton précédent
                if (page > 1) {
                    const btnPrev = document.createElement('button');
                    btnPrev.innerHTML = '&larr; Précédent';
                    btnPrev.className = 'px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 font-medium transition-colors';
                    btnPrev.onclick = () => { page--; paginerCreances(); };
                    pagDiv.appendChild(btnPrev);
                }
                
                // Numéros de pages
                for(let i = 1; i <= nbPages; i++) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    btn.className = `px-4 py-2 rounded-lg border font-medium transition-colors \${
                        i === page 
                        ? 'bg-blue-600 text-white border-blue-600' 
                        : 'bg-white text-blue-700 border-gray-300 hover:bg-blue-50'
                    }`;
                    btn.onclick = () => { page = i; paginerCreances(); };
                    pagDiv.appendChild(btn);
                }
                
                // Bouton suivant
                if (page < nbPages) {
                    const btnNext = document.createElement('button');
                    btnNext.innerHTML = 'Suivant &rarr;';
                    btnNext.className = 'px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 font-medium transition-colors';
                    btnNext.onclick = () => { page++; paginerCreances(); };
                    pagDiv.appendChild(btnNext);
                }
            }
            
            // Afficher le résumé
            const debut = (page - 1) * lignesParPage + 1;
            const fin = Math.min(page * lignesParPage, visibles.length);
            
            if (visibles.length > 0) {
                const resume = document.createElement('div');
                resume.className = 'text-sm text-gray-600 text-center mt-4';
                resume.textContent = `Affichage de ${debut} à ${fin} sur ${visibles.length} créance(s)`;
                pagDiv.appendChild(resume);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Initialisation : toutes les lignes sont visibles
            document.querySelectorAll('#body-creances tr').forEach(row => row.setAttribute('data-visible', '1'));
            paginerCreances();
            
            // Fermer les panneaux avec Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    fermerDetails();
                    fermerModalePaiement();
                }
            });
            
            // Fermer la modale en cliquant sur le fond
            document.getElementById('modalePaiement').addEventListener('click', (e) => {
                if (e.target === document.getElementById('modalePaiement')) {
                    fermerModalePaiement();
                }
            });
        });
    </script>
@endsection