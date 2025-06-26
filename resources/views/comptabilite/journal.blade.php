@extends('layouts.app')

@section('title', 'Journal Comptable')

@section('content')
@include('comptabilite.partials.nav')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Journal Comptable</h1>
                    <p class="text-blue-100">Suivi chronologique des écritures comptables</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('comptabilite.journal.export-pdf', request()->query()) }}" 
                       class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-gray-50 px-6 py-4 border-b">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                    <input type="date" name="date_debut" value="{{ $dateDebut }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_fin" value="{{ $dateFin }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Point de vente</label>
                    <select name="point_de_vente_id" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous</option>
                        @foreach($pointsDeVente as $pdv)
                            <option value="{{ $pdv->id }}" {{ $pointDeVenteId == $pdv->id ? 'selected' : '' }}>
                                {{ $pdv->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type d'opération</label>
                    <select name="type_operation" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Toutes</option>
                        <option value="vente" {{ $typeOperation == 'vente' ? 'selected' : '' }}>Vente</option>
                        <option value="paiement" {{ $typeOperation == 'paiement' ? 'selected' : '' }}>Paiement</option>
                        <option value="mouvement" {{ $typeOperation == 'mouvement' ? 'selected' : '' }}>Mouvement</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Filtrer
                </button>
            </form>
        </div>

        <!-- Liste des écritures -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Libellé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Point de vente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($journaux as $journal)
                        @php
                            $totalDebit = $journal->ecritures->sum('debit');
                            $totalCredit = $journal->ecritures->sum('credit');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($journal->date_ecriture)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ $journal->libelle }}</div>
                                @if($journal->reference)
                                    <div class="text-gray-500 text-xs">Réf: {{ $journal->reference }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $journal->pointDeVente->nom ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $typeColors = [
                                        'vente' => 'bg-green-100 text-green-800',
                                        'paiement' => 'bg-blue-100 text-blue-800',
                                        'mouvement' => 'bg-purple-100 text-purple-800'
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $typeColors[$journal->type_operation] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($journal->type_operation) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ number_format($journal->montant, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="voirDetail({{ $journal->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors">
                                    <i class="fas fa-eye mr-1"></i>Détail
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Détail des écritures (masqué par défaut) -->
                        <tr id="detail-{{ $journal->id }}" class="bg-gray-50 hidden">
                            <td colspan="6" class="px-6 py-4">
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <h4 class="font-medium text-gray-900 mb-3">Détail des écritures</h4>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                                <tr class="bg-gray-100">
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Compte</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700">Débit</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700">Crédit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($journal->ecritures as $ecriture)
                                                    <tr class="border-b border-gray-200">
                                                        <td class="px-3 py-2">
                                                            <div class="font-medium">{{ $ecriture->compte->numero }} - {{ $ecriture->compte->nom }}</div>
                                                            @if($ecriture->libelle_ecriture)
                                                                <div class="text-gray-500 text-xs">{{ $ecriture->libelle_ecriture }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-right {{ $ecriture->debit > 0 ? 'font-medium text-red-600' : 'text-gray-400' }}">
                                                            {{ $ecriture->debit > 0 ? number_format($ecriture->debit, 0, ',', ' ') : '-' }}
                                                        </td>
                                                        <td class="px-3 py-2 text-right {{ $ecriture->credit > 0 ? 'font-medium text-green-600' : 'text-gray-400' }}">
                                                            {{ $ecriture->credit > 0 ? number_format($ecriture->credit, 0, ',', ' ') : '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr class="bg-gray-100 font-medium">
                                                    <td class="px-3 py-2">Total</td>
                                                    <td class="px-3 py-2 text-right text-red-600">{{ number_format($totalDebit, 0, ',', ' ') }}</td>
                                                    <td class="px-3 py-2 text-right text-green-600">{{ number_format($totalCredit, 0, ',', ' ') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-book text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg">Aucune écriture comptable trouvée</p>
                                <p class="text-sm">Modifiez vos critères de recherche</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($journaux->hasPages())
            <div class="bg-white px-6 py-3 border-t">
                {{ $journaux->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function voirDetail(journalId) {
    const detailRow = document.getElementById(`detail-${journalId}`);
    if (detailRow.classList.contains('hidden')) {
        detailRow.classList.remove('hidden');
    } else {
        detailRow.classList.add('hidden');
    }
}
</script>
@endsection
