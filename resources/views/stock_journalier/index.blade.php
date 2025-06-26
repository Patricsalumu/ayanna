@extends('layouts.appvente')
@section('content')

<div class="max-w-7xl mx-auto px-6 py-6">
    <!-- Messages de statut -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-700 rounded-xl shadow-sm text-center font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(isset($message))
        <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl shadow-sm text-center font-semibold">
            {{ $message }}
        </div>
    @endif

    <!-- Zone consolidée : Titre, Filtres, Recherche et Export -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <!-- Titre et informations principales -->
        <div class="mb-6 text-center border-b border-gray-200 pb-4">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                Fiche de Stock Journalier
            </h1>
            <div class="flex justify-center items-center gap-6 text-gray-600">
                <span>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                @if(isset($nomPointDeVente))
                    <span class="text-gray-700 font-medium">{{ $nomPointDeVente }}</span>
                @endif
            </div>
        </div>

        <!-- Ligne des contrôles : Filtres, Recherche et Export -->
        <div class="flex flex-wrap gap-4 items-end justify-between">
            <!-- Filtres -->
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date :</label>
                    <input type="date" name="date" value="{{ $date }}" 
                           class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           onchange="this.form.submit()">
                </div>
                @if(isset($sessions) && $sessions->count() > 1)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Session :</label>
                        <select name="session" 
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                onchange="this.form.submit()">
                            @foreach($sessions as $sess)
                                @if($sess && strlen($sess) === 14 && ctype_digit($sess))
                                    <option value="{{ $sess }}" @if($sess == $session) selected @endif>
                                        {{ \Carbon\Carbon::createFromFormat('YmdHis', $sess)->format('d/m/Y H:i:s') }}
                                    </option>
                                @else
                                    <option value="{{ $sess }}" @if($sess == $session) selected @endif>
                                        Session inconnue ({{ $sess }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                @endif
            </form>

            <!-- Barre de recherche -->
            <div class="flex-1 max-w-md">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Recherche :</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           id="searchProduct" 
                           placeholder="Rechercher un produit..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           oninput="filterProducts()">
                </div>
            </div>
            
            <!-- Export PDF -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">&nbsp;</label>
                <a href="{{ route('stock_journalier.export_pdf', ['pointDeVente' => $pointDeVenteId, 'date' => $date, 'session' => $session ?? '']) }}" 
                   target="_blank" 
                   class="inline-flex items-center px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 shadow transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exporter PDF
                </a>
            </div>
        </div>
    </div>

    @if(count($produits) > 0)
    <!-- Tableau moderne -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full" id="stockTable">
                <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-4 text-left text-sm font-bold text-gray-700">ID</th>
                        <th class="px-4 py-4 text-left text-sm font-bold text-gray-700">Produit</th>
                        <th class="px-4 py-4 text-center text-sm font-bold text-gray-700">Q. Initiale</th>
                        <th class="px-4 py-4 text-center text-sm font-bold text-gray-700">Q. Ajoutée</th>
                        <th class="px-4 py-4 text-center text-sm font-bold text-gray-700">Q. Totale</th>
                        <th class="px-4 py-4 text-center text-sm font-bold text-gray-700">Q. Vendue</th>
                        <th class="px-4 py-4 text-center text-sm font-bold text-gray-700">Q. Restante</th>
                        <th class="px-4 py-4 text-right text-sm font-bold text-gray-700">Prix unitaire</th>
                        <th class="px-4 py-4 text-right text-sm font-bold text-gray-700">Total</th>
                        <th class="px-4 py-4 text-center text-sm font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($produits as $produit)
                    @php
                        $stock = $stocks->where('produit_id', $produit->id)->last();
                        $q_init = $stock->quantite_initiale ?? 0;
                        $q_ajout = $stock->quantite_ajoutee ?? 0;
                        $q_vendue = $stock->quantite_vendue ?? 0;
                        $q_total = $q_init + $q_ajout;
                        $q_reste = $stock->quantite_reste ?? ($q_total - $q_vendue);
                        $prix = $produit->prix_vente;
                        $total = $q_vendue * $prix;
                    @endphp
                    <tr class="hover:bg-blue-50 transition-colors duration-200 product-row" data-product-name="{{ strtolower($produit->nom) }}">
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                                {{ $stock->id ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-gray-900 font-semibold">{{ $produit->nom }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm font-medium">
                                {{ $q_init }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-medium">
                                {{ $q_ajout }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 text-sm font-bold">
                                {{ $q_total }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-800 text-sm font-medium">
                                {{ $q_vendue }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full {{ $q_reste > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} text-sm font-medium">
                                {{ $q_reste }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <span class="text-gray-900 font-semibold">{{ number_format($prix, 0, ',', ' ') }} <span class="text-sm text-gray-500">F</span></span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <span class="text-lg font-bold text-gray-900">{{ number_format($total, 0, ',', ' ') }} <span class="text-sm text-gray-500">F</span></span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <form method="POST" action="{{ url('stock-journalier/qtajoute') }}" class="inline-flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="produit_id" value="{{ $produit->id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="point_de_vente_id" value="{{ $pointDeVenteId }}">
                                <input type="number" 
                                       name="quantite_ajoutee" 
                                       value="{{ $q_ajout }}" 
                                       min="0" 
                                       class="border border-gray-300 rounded-lg px-2 py-1 w-16 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="submit" 
                                        class="inline-flex items-center p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow"
                                        title="Ajouter / Modifier">
                                    <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4'/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Total des ventes -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="text-right">
                @php
                    $totalVente = 0;
                    foreach($produits as $produit) {
                        $stock = $stocks->where('produit_id', $produit->id)->last();
                        $q_vendue = $stock->quantite_vendue ?? 0;
                        $prix = $produit->prix_vente;
                        $totalVente += $q_vendue * $prix;
                    }
                @endphp
                <span class="text-xl font-bold text-blue-700">
                    Total vente session : {{ number_format($totalVente, 0, ',', ' ') }} F
                </span>
            </div>
        </div>
    </div>
    @else
    <!-- Message aucun produit -->
    <div class="text-center py-16">
        <div class="max-w-md mx-auto">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-3">
                Aucun produit trouvé
            </h3>
            <p class="text-gray-500">
                Il n'y a aucun produit en stock pour cette session.
            </p>
        </div>
    </div>
    @endif
</div>

<!-- Modal moderne -->
<div id="modal-stock" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-200 scale-95">
        <!-- Header de la modale -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 mr-3">
                <h2 id="modal-title" class="text-lg font-bold text-gray-900">Ajout Stock</h2>
            </div>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Contenu de la modale -->
        <form id="modal-form" method="POST" action="{{ url('stock-journalier') }}" class="p-6">
            @csrf
            <input type="hidden" name="produit_id" id="modal-produit-id">
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="point_de_vente_id" value="{{ $pointDeVenteId }}">
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Quantité ajoutée :</label>
                <input type="number" 
                       name="quantite_ajoutee" 
                       id="modal-quantite-ajoutee" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       required min="0">
            </div>
            <button type="submit" 
                    class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition-colors shadow">
                Enregistrer
            </button>
        </form>
    </div>
</div>

<script>
    function openModal(produitId) {
        var row = document.querySelector('button[onclick="openModal(\''+produitId+'\')"]').closest('tr');
        var nomProduit = row.querySelector('td').innerText;
        document.getElementById('modal-title').innerText = 'Ajout Stock - ' + nomProduit;
        document.getElementById('modal-produit-id').value = produitId;
        document.getElementById('modal-quantite-ajoutee').value = '';
        document.getElementById('modal-stock').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Animation d'entrée
        setTimeout(() => {
            document.querySelector('#modal-stock > div').style.transform = 'scale(1)';
        }, 10);
    }
    
    function closeModal() {
        // Animation de sortie
        document.querySelector('#modal-stock > div').style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            document.getElementById('modal-stock').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 150);
    }
    
    function filterProducts() {
        const input = document.getElementById('searchProduct');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('stockTable');
        const rows = table?.querySelectorAll('.product-row') || [];
        let visibleRows = 0;
        
        rows.forEach(function(row) {
            const productName = row.getAttribute('data-product-name') || '';
            
            if (productName.includes(filter)) {
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Afficher un message si aucun résultat
        const tbody = table?.querySelector('tbody');
        let noResultRow = tbody?.querySelector('#no-result-row');
        
        if (visibleRows === 0 && filter !== '') {
            if (!noResultRow) {
                noResultRow = document.createElement('tr');
                noResultRow.id = 'no-result-row';
                noResultRow.innerHTML = `
                    <td colspan="10" class="px-6 py-8 text-center">
                        <div class="text-gray-500">
                            <div class="text-4xl mb-3">🔍</div>
                            <div class="text-lg font-medium mb-2">Aucun produit trouvé</div>
                            <div class="text-sm">Essayez de modifier votre recherche</div>
                        </div>
                    </td>
                `;
                tbody?.appendChild(noResultRow);
            }
            noResultRow.style.display = '';
        } else if (noResultRow) {
            noResultRow.style.display = 'none';
        }
    }
    
    // Fermer modal avec Échap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Fermer modal en cliquant sur le fond
    document.getElementById('modal-stock').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>
@endsection
