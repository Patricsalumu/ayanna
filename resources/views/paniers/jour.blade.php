@extends('layouts.appvente')
@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-2xl shadow-lg">
        @if(session('success'))
            <div class="mb-4 text-green-600 font-bold text-center">{{ session('success') }}</div>
        @endif
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-4">Factures des ventes</h2>
        <div class="mb-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <form method="GET" action="{{ route('paniers.jour') }}" class="flex flex-wrap items-center gap-3 w-full max-w-3xl">
                <input type="text" name="search" value="{{ $searchTerm ?? '' }}" placeholder="Rechercher client, serveuse, salle..." class="border rounded-full px-4 py-2 min-w-[220px] focus:outline-none focus:ring-2 focus:ring-blue-500">
                <label for="session_from" class="text-sm font-medium text-gray-700">Session de :</label>
                <select name="session_from" id="session_from" class="border rounded-full px-4 py-2 w-full md:w-auto focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Aucune --</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->session }}" {{ (isset($selectedSessionFrom) && $selectedSessionFrom == $session->session) ? 'selected' : '' }}>
                            {{ $session->validated_at ? \Carbon\Carbon::parse($session->validated_at)->format('d-m-y H:i') : $session->session }} - {{ $session->point_de_vente_nom }}
                        </option>
                    @endforeach
                </select>

                <label for="session_to" class="text-sm font-medium text-gray-700">Session à :</label>
                <select name="session_to" id="session_to" class="border rounded-full px-4 py-2 w-full md:w-auto focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Aucune --</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->session }}" {{ (isset($selectedSessionTo) && $selectedSessionTo == $session->session) ? 'selected' : '' }}>
                            {{ $session->validated_at ? \Carbon\Carbon::parse($session->validated_at)->format('d-m-y H:i') : $session->session }} - {{ $session->point_de_vente_nom }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="bg-blue-600 text-white rounded-full px-5 py-2 text-sm font-semibold hover:bg-blue-700 transition">Filtrer</button>

                <div class="ml-3">
                    <label for="payment_type" class="sr-only">Type de paiement</label>
                    <select name="payment_type" id="payment_type" class="border rounded-full px-4 py-2 focus:outline-none">
                        <option value="all" {{ (isset($selectedPaymentType) && $selectedPaymentType === 'all') ? 'selected' : (!isset($selectedPaymentType) ? 'selected' : '') }}>Tous</option>
                        <option value="cash" {{ (isset($selectedPaymentType) && $selectedPaymentType === 'cash') ? 'selected' : '' }}>Espèces</option>
                        <option value="credit" {{ (isset($selectedPaymentType) && $selectedPaymentType === 'credit') ? 'selected' : '' }}>Crédit</option>
                    </select>
                </div>
            </form>
            @if(!in_array(Auth::user()?->role, ['Serveuse', 'serveuse'], true))
            <a href="{{ route('paniers.jour.export-pdf') }}?session_from={{ $selectedSessionFrom ?? '' }}&session_to={{ $selectedSessionTo ?? '' }}&session={{ $selectedSession ?? '' }}"
                class="inline-flex items-center justify-center rounded-full bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 transition">
                Exporter PDF
            </a>
            @endif
        </div>
        @php
            $totalMontantsCalc = $totalMontants ?? 0;

            $totalPayeCalc = $totalPaye ?? 0;
            $totalCreditCalc = $totalCredit ?? 0;
        @endphp

        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-4">
                <div class="text-sm font-medium text-blue-700">Total paniers</div>
                <div id="totalPaniersDisplay" class="mt-1 text-2xl font-bold text-blue-900">{{ number_format($paniers->count(), 0, ',', ' ') }}</div>
            </div>
            <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-5 py-4">
                <div class="text-sm font-medium text-indigo-700">Total montant (TTC)</div>
                <div id="totalMontantDisplay" class="mt-1 text-2xl font-bold text-indigo-900">{{ optional(auth()->user()?->entreprise)->formatAmount($totalMontantsCalc ?? 0, true, 2) }}</div>
            </div>
            <div class="rounded-xl border border-green-100 bg-green-50 px-5 py-4">
                <div class="text-sm font-medium text-green-700">Total payé</div>
                <div id="totalPayeDisplay" class="mt-1 text-2xl font-bold text-green-900">{{ optional(auth()->user()?->entreprise)->formatAmount($totalPayeCalc ?? 0, true, 2) }}</div>
            </div>
            <div class="rounded-xl border border-yellow-100 bg-yellow-50 px-5 py-4">
                <div class="text-sm font-medium text-yellow-700">Total crédit</div>
                <div id="totalCreditDisplay" class="mt-1 text-2xl font-bold text-yellow-900">{{ optional(auth()->user()?->entreprise)->formatAmount($totalCreditCalc ?? 0, true, 2) }}</div>
            </div>
        </div>
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
        <div id="panierDetails" class="hidden mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Détails du panier validé</h3>
                    <p class="text-sm text-gray-500">Cliquez sur un panier validé pour voir les produits et le total.</p>
                </div>
                <button type="button" onclick="hidePanierDetails()" class="text-sm text-gray-500 hover:text-gray-800">Fermer</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 text-sm text-gray-700">
                <div><span class="font-semibold">Table :</span> <span id="detailTable"></span></div>
                <div><span class="font-semibold">Client :</span> <span id="detailClient"></span></div>
                <div><span class="font-semibold">Serveuse :</span> <span id="detailServeuse"></span></div>
                <div><span class="font-semibold">Salle :</span> <span id="detailSalle"></span></div>
                <div><span class="font-semibold">Ouvert à :</span> <span id="detailOuvertA"></span></div>
                <div><span class="font-semibold">Statut :</span> <span id="detailStatus"></span></div>
            </div>
            <div>
                <div class="mb-3 text-sm font-semibold text-gray-800">Produits</div>
                <div id="detailProduits" class="space-y-2"></div>
            </div>
            <div class="mt-4 border-t pt-4 text-right text-base font-semibold text-gray-900">
                Total : <span id="detailTotal"></span>
            </div>
        </div>
        <table class="w-full table-auto rounded-xl overflow-hidden border">
            <thead class="bg-blue-100 text-gray-700">
                <tr>
                    <th class="p-3 text-left">N° facture / panier</th>
                    <th class="p-3 text-left">Table</th>
                    <th class="p-3 text-left">Serveuse</th>
                    <th class="p-3 text-left">Client</th>
                    <th class="p-3 text-left">Ouvert à</th>
                    <th class="p-3 text-left">Statut</th>
                    <th class="p-3 text-left">Paiement</th>
                    <th class="p-3 text-left">Montant TTC sans remises</th>
                    <th class="p-3 text-left">Remises</th>
                    <th class="p-3 text-left">Net à payer</th>
                    <th class="p-3 text-left">Payé</th>
                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paniers as $panier)
                @php
                    $montantBrut = $panier->produits->sum(fn($p) => max(0, $p->pivot->quantite) * (($p->pivot->prix ?? $p->prix_vente) ?? 0));
                    $montantTtcSansRemises = (float) ($montantBrut + ($panier->total_tva ?? 0));
                    $remise = (float) ($panier->total_remise ?? $panier->remise ?? 0);
                    $netAPayer = (float) ($panier->total_ttc ?? max(0, $montantBrut - $remise + ($panier->total_tva ?? 0)));
                    $montantPaye = (float) ($panier->commande?->paiements?->sum('montant') ?? 0);
                    $panierDetails = [
                        'id' => $panier->id,
                        'reference' => $panier->commande?->id ? 'Facture #' . $panier->commande->id : 'Panier #' . $panier->id,
                        'table' => $panier->tableResto->numero ?? $panier->table_id,
                        'serveuse' => $panier->serveuse->name ?? '-',
                        'client' => $panier->client->nom ?? '-',
                            'salle' => $panier->tableResto->salle->nom ?? $panier->pointDeVente->nom ?? 'N/A',
                        'ouvert_a' => $panier->created_at->format('d/m H:i'),
                        'status' => $panier->status,
                        'remise' => $remise,
                        'net_a_payer' => $netAPayer,
                        'montant_paye' => $montantPaye,
                        'produits' => $panier->produits->map(function($prod) {
                            $unit = $prod->pivot->prix ?? $prod->prix_vente;
                            return [
                                'nom' => $prod->nom,
                                'quantite' => $prod->pivot->quantite,
                                'prix' => $unit,
                                'total' => max(0, $prod->pivot->quantite) * ($unit ?? 0),
                            ];
                        })->toArray(),
                        'total' => $netAPayer,
                    ];
                @endphp
                <tr class="hover:bg-gray-100 {{ $panier->status !== 'annulé' ? 'cursor-pointer' : 'opacity-60' }} panier-row"
                    data-url="{{ $panier->status === 'en_cours' ? route('vente.catalogue', ['pointDeVente' => $panier->point_de_vente_id]) . '?table_id=' . $panier->table_id : '' }}"
                    data-panier-status="{{ $panier->status }}"
                    data-panier='@json($panierDetails)'
                    data-produits="{{ strtolower(collect($panier->produits)->pluck('nom')->implode(',')) }}">
                    <td class="p-3 font-semibold">{{ $panier->commande?->id ? 'Facture #' . $panier->commande->id : 'Panier #' . $panier->id }}</td>
                    <td class="p-3">{{ $panier->tableResto->numero ?? $panier->table_id }}</td>
                    <td class="p-3">{{ $panier->serveuse->name ?? '-' }}</td>
                    <td class="p-3">{{ $panier->client->nom ?? '-' }}</td>
                    <td class="p-3">{{ $panier->created_at->format('d/m H:i') }}</td>
                    <td class="p-3">{{ $panier->status }}</td>
                    <td class="p-3">
                        @php
                            $modeRaw = $panier->commande?->mode_paiement ?? $panier->mode_paiement ?? 'compte_client';
                            $modeNorm = strtolower(str_replace(['_', '-', ' ', 'é', 'è', 'ê'], ['', '', '', 'e', 'e', 'e'], $modeRaw));
                            $isCredit = str_contains($modeNorm, 'compte') || in_array($modeNorm, ['credit', 'compteclient', 'compte_client'], true);
                            $modeLabel = $isCredit ? 'Crédit' : 'Espèces';
                            $modeClass = $isCredit ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
                        @endphp
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $modeClass }}">{{ $modeLabel }}</span>
                    </td>
                    <td class="p-3">{{ optional(auth()->user()?->entreprise)->formatAmount($montantTtcSansRemises, true, 2) }}</td>
                    <td class="p-3">{{ optional(auth()->user()?->entreprise)->formatAmount($remise, true, 2) }}</td>
                    <td class="p-3 font-semibold">{{ optional(auth()->user()?->entreprise)->formatAmount($netAPayer, true, 2) }}</td>
                    <td class="p-3 text-green-700 font-semibold">{{ optional(auth()->user()?->entreprise)->formatAmount($montantPaye, true, 2) }}</td>
                    <td class="p-3">
                        @if($panier->status === 'en_cours')
                            @if(!in_array(Auth::user()->role ?? null, ['comptoiriste','serveuse']))
                                <form method="POST" action="{{ route('paniers.annuler', $panier->id) }}" class="annuler-form">
                                    @csrf
                                    @method("PATCH")
                                    <input type="hidden" name="from" value="jour">
                                    <button type="button" 
                                        class="bg-red-600 text-white rounded-full text-xs px-3 py-1 hover:bg-red-700 annuler-btn"
                                        data-table="{{ $panier->tableResto->nom ?? 'Table ' . $panier->table_id }}"
                                        data-montant="{{ optional(auth()->user()?->entreprise)->formatAmount($montantTtcSansRemises, true, 2) }}">
                                        Annuler
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        @elseif(app(\App\Services\PermissionService::class)->canPrintReceipt(auth()->user()) && $panier->commande && in_array($panier->commande->statut, ['validé', 'payé'], true))
                            <a href="{{ route('creances.imprimer', $panier->commande->id) }}?auto_print=1"
                               target="_blank"
                               onclick="event.stopPropagation()"
                               class="inline-flex items-center rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-700"
                               title="Réimprimer le reçu de paiement">
                                Réimprimer reçu
                            </a>
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
                
                const status = this.getAttribute('data-panier-status');
                const url = this.getAttribute('data-url');
                const panierData = this.getAttribute('data-panier');

                if (status === 'en_cours' && url && url !== '') {
                    window.location = url;
                    return;
                }

                if (status !== 'en_cours' && panierData) {
                    try {
                        const panier = JSON.parse(panierData);
                        showPanierDetails(panier);
                    } catch (error) {
                        console.error('Impossible de parser les détails du panier :', error);
                    }
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
                const salle = tds[3]?.textContent.toLowerCase();
                // Respect payment type filter client-side
                const paymentFilter = (document.querySelector('select[name="payment_type"]')?.value || '').toLowerCase();
                const modeCellText = tds[6]?.textContent.toLowerCase() || '';
                let paymentMatch = true;
                if (paymentFilter && paymentFilter !== 'all') {
                    if (paymentFilter === 'credit') paymentMatch = modeCellText.includes('crédit') || modeCellText.includes('credit');
                    else paymentMatch = ! (modeCellText.includes('crédit') || modeCellText.includes('credit'));
                }

                if ((client.includes(filter) || serveuse.includes(filter) || produits.includes(filter) || tableNom.includes(filter) || salle.includes(filter)) && paymentMatch) {
                    trs[i].style.display = "";
                } else {
                    trs[i].style.display = "none";
                }
            }
        }
        // Mettre à jour les KPI après filtrage
        updateKpisFromVisibleRows();
    }

    const currencySymbol = @json(optional(auth()->user()?->entreprise)->devise ?? '$');

    function formatCurrency(value) {
        const amount = Number(value || 0);
        return `${amount.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currencySymbol}`;
    }

    function showPanierDetails(panier) {
        document.getElementById('detailTable').textContent = panier.table;
        document.getElementById('detailClient').textContent = panier.client;
        document.getElementById('detailServeuse').textContent = panier.serveuse;
        document.getElementById('detailSalle').textContent = panier.salle;
        document.getElementById('detailOuvertA').textContent = panier.ouvert_a;
        document.getElementById('detailStatus').textContent = panier.status;

        const produitsContainer = document.getElementById('detailProduits');
        produitsContainer.innerHTML = '';
        panier.produits.forEach(item => {
            const produitRow = document.createElement('div');
            produitRow.className = 'flex justify-between bg-gray-50 rounded-xl p-3';
            produitRow.innerHTML = `
                <div>
                    <div class="font-semibold text-gray-900">${item.nom}</div>
                    <div class="text-xs text-gray-500">x${item.quantite} · ${formatCurrency(item.prix)}</div>
                </div>
                <div class="font-semibold text-gray-900">${formatCurrency(item.total)}</div>
            `;
            produitsContainer.appendChild(produitRow);
        });

        document.getElementById('detailTotal').textContent = formatCurrency(panier.total);
        document.getElementById('panierDetails').classList.remove('hidden');
        document.getElementById('panierDetails').scrollIntoView({ behavior: 'smooth' });
    }

    // Met à jour les KPI (totaux) en fonction des lignes visibles
    function updateKpisFromVisibleRows() {
        const table = document.querySelector('table');
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        let visibleCount = 0;
        let totalMontant = 0;
        let totalPaye = 0;
        let totalCredit = 0;

        rows.forEach(row => {
            if (row.style.display === 'none') return;
            const tds = row.querySelectorAll('td');
            if (!tds.length) return;
            // Montant is in column index 7 (0-based)
            const montantText = tds[7]?.textContent.trim() || '0';
            const montant = parseFloat(montantText.replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;
            const modeText = (tds[6]?.textContent || '').toLowerCase();

            visibleCount += 1;
            totalMontant += montant;
            if (modeText.includes('crédit') || modeText.includes('credit')) {
                totalCredit += montant;
            } else {
                totalPaye += montant;
            }
        });

        // Mettre à jour l'affichage
        document.getElementById('totalPaniersDisplay').textContent = new Intl.NumberFormat('fr-FR').format(visibleCount);
        document.getElementById('totalMontantDisplay').textContent = formatCurrency(totalMontant);
        document.getElementById('totalPayeDisplay').textContent = formatCurrency(totalPaye);
        document.getElementById('totalCreditDisplay').textContent = formatCurrency(totalCredit);
    }

    function hidePanierDetails() {
        document.getElementById('panierDetails').classList.add('hidden');
    }
</script>
@endsection
