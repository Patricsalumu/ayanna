@extends('layouts.appsalle')
@section('content')
<div class="max-w-8xl mx-auto mt-8 bg-white rounded-xl shadow-lg p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <a href="{{ route('comptes.index') }}" class="inline-flex items-center gap-1 px-4 py-2 rounded bg-gray-200 text-blue-700 font-semibold hover:bg-blue-100 shadow order-1 sm:order-none">
            <i data-lucide="arrow-left"></i> Retour
        </a>
        <div class="flex-1 flex flex-col gap-2 order-3 sm:order-none">
            <h1 class="text-2xl font-bold text-blue-700 flex items-center gap-2">
                <i data-lucide="book-open" class="w-6 h-6 text-blue-500"></i>
                Mouvements du compte <span class="text-indigo-700">{{ $compte->numero }}</span>
            </h1>
            <div class="text-gray-500 text-sm mt-1">Nom : <span class="font-semibold">{{ $compte->nom }}</span></div>
        </div>
        <form method="POST" action="{{ route('comptes.mouvements.ajouter', $compte) }}" class="mb-0 flex flex-col sm:flex-row gap-2 items-end order-2 sm:order-none">
            @csrf
            <div>
                <label class="block mb-1 font-semibold">Montant</label>
                <input type="number" step="0.01" name="montant" class="border rounded px-3 py-2 w-32 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block mb-1 font-semibold">Type</label>
                <select name="type" class="border rounded px-3 py-2 w-28 focus:border-blue-500" required>
                    <option value="credit">Crédit</option>
                    <option value="debit">Débit</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block mb-1 font-semibold">Libellé</label>
                <input type="text" name="libele" class="border rounded px-3 py-2 w-full focus:border-blue-500" required>
            </div>
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700 flex items-center gap-1">
                <i data-lucide="plus-circle"></i> Ajouter
            </button>
        </form>
    </div>

    {{-- Solde courant et totaux --}}
    <div class="mb-6 flex flex-wrap items-center gap-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg px-6 py-3 flex flex-col items-center shadow-sm">
            <span class="text-xs text-gray-500">Solde courant</span>
            <span id="soldeCourant" class="text-2xl font-bold text-blue-700">
                {{ number_format($mouvements->where('type','credit')->sum('montant') - $mouvements->where('type','debit')->sum('montant'), 2, ',', ' ') }} F
            </span>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg px-6 py-3 flex flex-col items-center shadow-sm">
            <span class="text-xs text-gray-500">Total crédits</span>
            <span id="totalCredits" class="text-xl font-bold text-green-700">
                {{ number_format($mouvements->where('type','credit')->sum('montant'), 2, ',', ' ') }} F
            </span>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg px-6 py-3 flex flex-col items-center shadow-sm">
            <span class="text-xs text-gray-500">Total débits</span>
            <span id="totalDebits" class="text-xl font-bold text-red-700">
                {{ number_format($mouvements->where('type','debit')->sum('montant'), 2, ',', ' ') }} F
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 text-green-700 bg-green-100 rounded px-4 py-2">{{ session('success') }}</div>
    @endif

    <!-- Barre de recherche et filtre -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div class="flex-1 flex items-center gap-2">
            <input id="searchInput" type="text" placeholder="Rechercher date, heure, libellé..." class="w-full rounded-full border border-gray-300 pl-4 pr-10 py-2 focus:outline-none focus:border-gray-400 shadow-sm" />
            <span class="-ml-8 text-gray-400">🔍</span>
        </div>
        <div class="flex gap-2 items-center">
            <input type="date" id="filterDate" class="rounded border-gray-300 px-3 py-2 focus:border-blue-500" />
            <select id="filterType" class="rounded border-gray-300 px-3 py-2 focus:border-blue-500">
                <option value="">Tous</option>
                <option value="credit">Crédit</option>
                <option value="debit">Débit</option>
            </select>
            <form id="exportPdfForm" method="GET" action="{{ route('comptes.mouvements.exportpdf', $compte) }}" target="_blank">
                <input type="hidden" name="date" id="exportDate">
                <input type="hidden" name="type" id="exportType">
                <input type="hidden" name="search" id="exportSearch">
                <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white font-semibold hover:bg-indigo-700 flex items-center gap-1">
                    <i data-lucide="file-down"></i> Exporter PDF
                </button>
            </form>
        </div>
    </div>

    {{-- Table des mouvements --}}
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded-lg shadow-sm">
            <thead>
                <tr class="bg-blue-100 text-blue-700 text-sm">
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Montant</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Libellé</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody id="mouvementsTableBody">
                @forelse($mouvements as $mvt)
                    <tr class="border-b hover:bg-blue-50 transition" data-type="{{ $mvt->type }}">
                        <td class="px-4 py-2 text-center text-xs date-cell">{{ $mvt->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 text-right font-bold">
                            <span class="@if($mvt->type == 'credit') text-green-600 @else text-red-600 @endif">
                                {{ number_format($mvt->montant, 2, ',', ' ') }} F
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($mvt->type == 'credit')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold border border-green-200">
                                    <i data-lucide="arrow-down-circle" class="w-4 h-4"></i> Crédit
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-semibold border border-red-200">
                                    <i data-lucide="arrow-up-circle" class="w-4 h-4"></i> Débit
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $mvt->libele }}</td>
                        <td class="px-4 py-2">
                            <form action="{{ route('comptes.mouvements.supprimer', $mvt) }}" method="POST" onsubmit="return confirm('Supprimer ce mouvement ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 rounded bg-red-600 text-white font-semibold hover:bg-red-700 flex items-center gap-1">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-400 py-8">Aucun mouvement.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>lucide.createIcons();
// Recherche dynamique et filtres
const searchInput = document.getElementById('searchInput');
const filterDate = document.getElementById('filterDate');
const filterType = document.getElementById('filterType');
const tableBody = document.getElementById('mouvementsTableBody');
searchInput?.addEventListener('input', filterRows);
filterDate?.addEventListener('change', filterRows);
filterType?.addEventListener('change', filterRows);
function filterRows() {
    const search = searchInput.value.trim().toLowerCase();
    const date = filterDate.value;
    const type = filterType.value;
    let totalCredit = 0;
    let totalDebit = 0;
    Array.from(tableBody.rows).forEach(row => {
        const dateCell = row.querySelector('.date-cell')?.textContent || '';
        const libele = row.cells[3]?.textContent.toLowerCase() || '';
        const rowType = row.getAttribute('data-type');
        const montantCell = row.cells[1]?.textContent.replace(/[^\d,.-]/g, '').replace(',', '.');
        const montant = parseFloat(montantCell) || 0;
        let show = true;
        if (search && !(dateCell.toLowerCase().includes(search) || libele.includes(search))) show = false;
        if (date) {
            const rowDate = dateCell.split(' ')[0].split('/').reverse().join('-');
            if (rowDate !== date) show = false;
        }
        if (type && rowType !== type) show = false;
        row.style.display = show ? '' : 'none';
        if (show) {
            if (rowType === 'credit') totalCredit += montant;
            if (rowType === 'debit') totalDebit += montant;
        }
    });
    // Mise à jour des totaux dynamiques
    document.getElementById('totalCredits').textContent = totalCredit.toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' F';
    document.getElementById('totalDebits').textContent = totalDebit.toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' F';
    document.getElementById('soldeCourant').textContent = (totalCredit - totalDebit).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' F';
}

// Synchronisation des filtres avec le formulaire d'export PDF
const exportPdfForm = document.getElementById('exportPdfForm');
const exportDate = document.getElementById('exportDate');
const exportType = document.getElementById('exportType');
const exportSearch = document.getElementById('exportSearch');
[searchInput, filterDate, filterType].forEach(el => el?.addEventListener('input', syncExportFilters));
function syncExportFilters() {
    exportDate.value = filterDate.value;
    exportType.value = filterType.value;
    exportSearch.value = searchInput.value;
}
syncExportFilters(); // initial
</script>
@endsection