@extends('layouts.appvente')
@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto bg-white p-6 rounded-2xl shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('paniers.show') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour aux paniers
            </a>
            <h2 class="text-2xl font-bold text-gray-800 text-center">Historique des impressions de paniers</h2>
            <div class="w-36"></div> <!-- Élément vide pour équilibrer la mise en page -->
        </div>
        
        <!-- Filtres et recherche -->
        <div class="bg-gray-50 p-4 mb-6 rounded-xl shadow-inner">
            <form action="{{ route('paniers.historique-impressions') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                        <input type="date" id="date_debut" name="date_debut" value="{{ request('date_debut', now()->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                        <input type="date" id="date_fin" name="date_fin" value="{{ request('date_fin', now()->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Utilisateur</label>
                        <select name="user_id" id="user_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Tous les utilisateurs</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
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
                                placeholder="Rechercher par produit..."
                                class="w-full border rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ request('search') }}"
                            />
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg flex items-center justify-center gap-2 transition h-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filtrer
                        </button>
                        <a href="{{ route('paniers.historique-impressions') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition h-full">
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
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-16 bg-gradient-to-l from-purple-100 to-transparent opacity-50"></div>
                <div class="relative z-10">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-purple-700 font-medium">Total des impressions</div>
                            <div class="text-2xl font-bold">{{ $impressions->total() }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-16 bg-gradient-to-l from-blue-100 to-transparent opacity-50"></div>
                <div class="relative z-10">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-blue-700 font-medium">Montant total imprimé</div>
                            <div class="text-2xl font-bold">{{ number_format($montantTotal, 0, ',', ' ') }} F</div>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-green-700 font-medium">Paniers actifs</div>
                            <div class="text-2xl font-bold">{{ $paniersActifs }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 rounded-xl p-4 border border-red-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-16 bg-gradient-to-l from-red-100 to-transparent opacity-50"></div>
                <div class="relative z-10">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-red-700 font-medium">Paniers supprimés</div>
                            <div class="text-2xl font-bold">{{ $paniersSupprimes }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($impressions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full table-auto rounded-xl overflow-hidden border">
                <thead class="bg-purple-100 text-gray-700">
                    <tr>
                        <th class="p-3 text-left">ID Impression</th>
                        <th class="p-3 text-left">ID Panier</th>
                        <th class="p-3 text-left">Utilisateur</th>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-right">Montant</th>
                        <th class="p-3 text-center">État</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($impressions as $impression)
                    <tr class="hover:bg-gray-100 {{ $impression->panier_supprime ? 'border-l-4 border-red-500' : 'border-l-4 border-green-500' }}">
                        <td class="p-3">
                            <div class="font-medium text-gray-900">#{{ $impression->id }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium">{{ $impression->panier_id }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium text-blue-600">{{ $impression->user->name ?? 'N/A' }}</div>
                        </td>
                        <td class="p-3">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ \Carbon\Carbon::parse($impression->created_at)->format('d/m/Y') }}</span>
                                <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($impression->created_at)->format('H:i') }}</span>
                            </div>
                        </td>
                        <td class="p-3 text-right font-medium">{{ number_format($impression->montant_total, 0, ',', ' ') }} F</td>
                        <td class="p-3 text-center">
                            @if($impression->panier_supprime)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <span class="w-2 h-2 bg-red-400 rounded-full mr-1"></span>
                                    Panier supprimé
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>
                                    Panier actif
                                </span>
                            @endif
                        </td>
                        <td class="p-3 flex justify-center space-x-2">
                            <button class="bg-blue-600 text-white rounded-full p-2 hover:bg-blue-700 view-products-btn" 
                                title="Voir produits" 
                                data-impression-id="{{ $impression->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
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
            {{ $impressions->appends(request()->query())->links() }}
        </div>

        @else
            <div class="text-center py-12 bg-gray-50 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                </svg>
                <h3 class="text-xl font-medium text-gray-600 mb-2">Aucune impression trouvée</h3>
                <p class="text-gray-500">Essayez de modifier vos critères de recherche</p>
            </div>
        @endif
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Boutons pour voir les produits d'une impression
        document.querySelectorAll('.view-products-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                const impressionId = btn.getAttribute('data-impression-id');
                showImpressionProduits(impressionId);
            });
        });
    });
    
    function showImpressionProduits(impressionId) {
        const modal = document.getElementById('impressionProduitsModal');
        const contentDiv = document.getElementById('impressionProduitsContent');
        
        // Afficher la modale avec le loader
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Animation d'entrée
        setTimeout(() => {
            document.querySelector('#impressionProduitsModal > div').style.transform = 'scale(1)';
        }, 10);
        
        // Charger les produits via fetch
        fetch(`/paniers/impression/${impressionId}/produits`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Construire le contenu HTML avec les produits
                let html = `<div class="space-y-4">`;
                
                // En-tête avec infos générales
                html += `<div class="flex flex-col md:flex-row justify-between items-start gap-4 border-b pb-4">
                    <div>
                        <h3 class="font-bold text-lg">Impression #${impressionId}</h3>
                        <p class="text-gray-600">Date: ${new Date(data.created_at).toLocaleDateString('fr-FR')} à ${new Date(data.created_at).toLocaleTimeString('fr-FR')}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-gray-600">Utilisateur</div>
                        <div class="font-semibold text-blue-600">${data.user?.name || 'N/A'}</div>
                    </div>
                </div>`;
                
                // Liste des produits
                html += `<div>
                    <h4 class="font-semibold mb-2">Produits imprimés</h4>
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
                        const quantite = produit.qte;
                        const prix = parseFloat(produit.prix);
                        const sousTotal = quantite * prix;
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
                    </table>
                </div>`;
                
                html += `</div>`;
                contentDiv.innerHTML = html;
            })
            .catch(error => {
                console.error("Erreur lors du chargement des produits de l'impression:", error);
                contentDiv.innerHTML = `<div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Erreur</h3>
                    <p class="text-gray-600">Impossible de charger les détails des produits.</p>
                </div>`;
            });
    }
    
    function hideImpressionProduitsModal() {
        const modal = document.getElementById('impressionProduitsModal');
        document.querySelector('#impressionProduitsModal > div').style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 150);
    }
</script>
@endsection
