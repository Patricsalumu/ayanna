@extends('layouts.appvente')
@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-2xl shadow-lg">
        @if(session('success'))
            <div class="mb-4 text-green-600 font-bold text-center">{{ session('success') }}</div>
        @endif
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-4">Paniers du jour</h2>
        <div class="mb-6 flex justify-center">
            <input
                type="text"
                id="search"
                placeholder="Rechercher client, serveuse, point de vente..."
                class="border rounded-full px-4 py-2 w-full max-w-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                oninput="filterPaniers()"
            />
        </div>
        @if($paniers->count() > 0)
        <table class="w-full table-auto rounded-xl overflow-hidden border">
            <thead class="bg-blue-100 text-gray-700">
                <tr>
                    <th class="p-3 text-left">Table</th>
                    <th class="p-3 text-left">Serveuse</th>
                    <th class="p-3 text-left">Client</th>
                    <th class="p-3 text-left">Point de vente</th>
                    <th class="p-3 text-left">Ouvert à</th>
                    <th class="p-3 text-left">Statut</th>
                    <th class="p-3 text-left">Montant</th>
                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paniers as $panier)
                <tr class="hover:bg-gray-100 {{ $panier->status === 'en_cours' ? 'cursor-pointer' : 'opacity-60' }} panier-row"
                    data-url="{{ $panier->status === 'en_cours' ? route('vente.catalogue', ['pointDeVente' => $panier->point_de_vente_id]) . '?table_id=' . $panier->table_id : '' }}"
                    data-produits="{{ strtolower(collect($panier->produits)->pluck('nom')->implode(',')) }}">
                    <td class="p-3">{{ $panier->tableResto->numero ?? $panier->table_id }}</td>
                    <td class="p-3">{{ $panier->serveuse->name ?? '-' }}</td>
                    <td class="p-3">{{ $panier->client->nom ?? '-' }}</td>
                    <td class="p-3">
                        <div class="text-sm">
                            <div class="font-medium text-blue-600">{{ $panier->pointDeVente->nom ?? 'N/A' }}</div>
                        </div>
                    </td>
                    <td class="p-3">{{ $panier->created_at->format('H:i') }}</td>
                    <td class="p-3">{{ $panier->status }}</td>
                    <td class="p-3">{{ number_format($panier->produits->sum(fn($p) => max(0, $p->pivot->quantite) * $p->prix_vente), 0, ',', ' ') }} F</td>
                    <td class="p-3">
                        @if($panier->status === 'en_cours')
                        <form method="POST" action="{{ route('paniers.annuler', $panier->id) }}" class="annuler-form">
                            @csrf
                            @method("PATCH")
                            <input type="hidden" name="from" value="jour">
                            <button type="button" 
                                class="bg-red-600 text-white rounded-full text-xs px-3 py-1 hover:bg-red-700 annuler-btn"
                                data-table="{{ $panier->tableResto->nom ?? 'Table ' . $panier->table_id }}"
                                data-montant="{{ number_format($panier->produits->sum(fn($p) => max(0, $p->pivot->quantite) * $p->prix_vente), 0, ',', ' ') }} F">
                                Annuler
                            </button>
                        </form>
                        @else
                        <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center text-gray-500 text-lg font-semibold mt-6">Aucun panier trouvé</div>
        @endif
    </div>
</div>

<!-- Modale de confirmation -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-200 scale-95">
        <!-- Header de la modale -->
        <div class="flex items-center p-6 border-b border-gray-200">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 mr-3">
            <h3 class="text-lg font-bold text-gray-900">Confirmation d'annulation</h3>
        </div>
        
        <!-- Contenu de la modale -->
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-900 font-medium">Êtes-vous sûr de vouloir annuler ce panier ?</p>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="font-medium" id="tableInfo"></span><br>
                        <span class="text-red-600 font-medium" id="montantInfo"></span>
                    </p>
                </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                <p class="text-sm text-yellow-800">
                    ⚠️ Cette action est irréversible. Le panier sera définitivement supprimé.
                </p>
            </div>
        </div>
        
        <!-- Footer de la modale -->
        <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
            <button onclick="hideConfirmModal()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                Annuler
            </button>
            <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                Supprimer le panier
            </button>
        </div>
    </div>
</div>

<script>
    let formToSubmit = null;

    // Attendre que le DOM soit chargé
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM chargé, initialisation des événements...');
        
        // Ajouter des événements directement aux boutons d'annulation
        const annulerButtons = document.querySelectorAll('.annuler-btn');
        console.log('Boutons trouvés:', annulerButtons.length);
        
        annulerButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                console.log('Clic sur bouton annuler détecté');
                e.stopPropagation(); // Empêche la propagation vers la ligne
                
                const form = this.closest('form');
                const tableNom = this.getAttribute('data-table');
                const montant = this.getAttribute('data-montant');
                
                console.log('Données:', { form, tableNom, montant });
                showConfirmModal(form, tableNom, montant);
            });
        });
        
        // Gérer les clics sur les lignes du tableau
        const panierRows = document.querySelectorAll('.panier-row');
        panierRows.forEach(function(row) {
            row.addEventListener('click', function(e) {
                // Vérifier si le clic vient d'un bouton ou d'un formulaire
                if (e.target.closest('.annuler-form') || e.target.classList.contains('annuler-btn')) {
                    console.log('Clic sur formulaire/bouton, propagation arrêtée');
                    return;
                }
                
                console.log('Clic sur ligne détecté');
                const url = this.getAttribute('data-url');
                if (url && url !== '') {
                    window.location = url;
                }
            });
        });
    });

    function showConfirmModal(form, tableNom, montant) {
        console.log('Affichage de la modale:', { tableNom, montant });
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
        console.log('Fermeture de la modale');
        // Animation de sortie
        document.querySelector('#confirmModal > div').style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            document.getElementById('confirmModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            formToSubmit = null;
        }, 150);
    }

    function confirmDelete() {
        console.log('Confirmation de suppression');
        if (formToSubmit) {
            // Ajouter un indicateur de chargement
            const submitBtn = document.querySelector('#confirmModal button[onclick="confirmDelete()"]');
            const originalText = submitBtn.textContent;
            submitBtn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>Suppression...';
            submitBtn.disabled = true;
            
            formToSubmit.submit();
        }
    }

    // Fermer la modale avec Échap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideConfirmModal();
        }
    });

    // Fermer la modale en cliquant sur le fond
    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideConfirmModal();
        }
    });

    function filterPaniers() {
        const input = document.getElementById('search');
        const filter = input.value.toLowerCase();
        const table = document.querySelector("table");
        const trs = table?.getElementsByTagName("tr") || [];
        for (let i = 1; i < trs.length; i++) {
            const tds = trs[i]?.getElementsByTagName("td") || [];
            const produits = trs[i]?.getAttribute("data-produits") || "";
            if (tds.length > 0) {
                const client = tds[2]?.textContent.toLowerCase();
                const serveuse = tds[1]?.textContent.toLowerCase();
                const tableNom = tds[0]?.textContent.toLowerCase();
                const pointDeVente = tds[3]?.textContent.toLowerCase();
                if (client.includes(filter) || serveuse.includes(filter) || produits.includes(filter) || tableNom.includes(filter) || pointDeVente.includes(filter)) {
                    trs[i].style.display = "";
                } else {
                    trs[i].style.display = "none";
                }
            }
        }
    }
</script>
@endsection
