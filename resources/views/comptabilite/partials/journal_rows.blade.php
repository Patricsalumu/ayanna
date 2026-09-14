@forelse($journaux as $journal)
    @php
        $totalDebit = $journal->ecritures->sum('debit');
        $totalCredit = $journal->ecritures->sum('credit');
        $estAnnule = $journal->statut === 'annule';
        $estBrouillon = $journal->statut === 'brouillon';
    @endphp
    <tr class="hover:bg-gray-50 {{ $estAnnule ? 'bg-gray-100 opacity-60' : '' }}">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            <div>{{ \Carbon\Carbon::parse($journal->date_ecriture)->format('d/m/Y') }}</div>
            @if(!empty($journal->heure_ecriture))
                <div class="text-gray-500 text-xs">
                    {{ \Carbon\Carbon::parse($journal->heure_ecriture)->format('H:i:s') }}
                </div>
            @endif
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
                    'achat' => 'bg-orange-100 text-orange-800',
                    'od' => 'bg-indigo-100 text-indigo-800',
                    'caisse' => 'bg-cyan-100 text-cyan-800',
                    'paiement' => 'bg-blue-100 text-blue-800',
                    'mouvement' => 'bg-purple-100 text-purple-800'
                ];
            @endphp
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $typeColors[$journal->type_operation] ?? 'bg-gray-100 text-gray-800' }}">
                {{ ucfirst($journal->type_operation) }}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
            @currency($journal->montant_total)
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="flex flex-col gap-2">
                <button onclick="voirDetail({{ $journal->id }})" 
                        class="text-blue-600 hover:text-blue-900 transition-colors text-left">
                    <i class="fas fa-eye mr-1"></i>Détail
                </button>
                @if($estBrouillon)
                    <form method="POST" action="{{ route('comptabilite.journal.valider', $journal) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-emerald-600 hover:text-emerald-900 transition-colors text-left">
                            <i class="fas fa-check mr-1"></i>Valider
                        </button>
                    </form>
                    <button type="button"
                            onclick="ouvrirConfirmationAnnulation('{{ route('comptabilite.journal.annuler', $journal) }}', '{{ addslashes($journal->libelle) }}')"
                            class="text-red-600 hover:text-red-900 transition-colors text-left">
                        <i class="fas fa-times mr-1"></i>Annuler
                    </button>
                @elseif($estAnnule)
                    <span class="text-gray-500">Annulée</span>
                @else
                    <span class="text-green-600">Validée</span>
                @endif
            </div>
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
                                        @if($ecriture->debit > 0)
                                            @currency($ecriture->debit)
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right {{ $ecriture->credit > 0 ? 'font-medium text-green-600' : 'text-gray-400' }}">
                                        @if($ecriture->credit > 0)
                                            @currency($ecriture->credit)
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-100 font-medium">
                                <td class="px-3 py-2">Total</td>
                                <td class="px-3 py-2 text-right text-red-600">@currency($totalDebit)</td>
                                <td class="px-3 py-2 text-right text-green-600">@currency($totalCredit)</td>
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
