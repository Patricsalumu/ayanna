@extends('layouts.app')

@section('title', 'Grand Livre - ' . $compte->nom)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">{{ $compte->numero }} - {{ $compte->nom }}</h1>
                    <p class="text-green-100">Détail des mouvements du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
                </div>
                <a href="{{ route('comptabilite.grand-livre') }}?date_debut={{ $dateDebut }}&date_fin={{ $dateFin }}" 
                   class="bg-white text-green-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
                </a>
            </div>
        </div>

        <!-- Informations du compte -->
        <div class="bg-gray-50 px-6 py-4 border-b">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-3 rounded-lg">
                    <div class="text-sm text-gray-600">Type de compte</div>
                    <div class="font-semibold text-gray-900">{{ ucfirst($compte->type) }}</div>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <div class="text-sm text-gray-600">Classe comptable</div>
                    <div class="font-semibold text-gray-900">{{ $compte->classeComptable->nom ?? 'N/A' }}</div>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <div class="text-sm text-gray-600">Solde initial</div>
                    <div class="font-semibold {{ $soldeInitial >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($soldeInitial, 0, ',', ' ') }} FC
                    </div>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    @php
                        $debitTotal = $ecritures->sum('debit');
                        $creditTotal = $ecritures->sum('credit');
                        if ($compte->type === 'actif') {
                            $soldeFinal = $soldeInitial + $debitTotal - $creditTotal;
                        } else {
                            $soldeFinal = $soldeInitial + $creditTotal - $debitTotal;
                        }
                    @endphp
                    <div class="text-sm text-gray-600">Solde final</div>
                    <div class="font-semibold {{ $soldeFinal >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($soldeFinal, 0, ',', ' ') }} FC
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des écritures -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Libellé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Débit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Crédit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Solde</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $soldeProgressif = $soldeInitial; @endphp
                    
                    <!-- Ligne solde initial -->
                    <tr class="bg-blue-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($dateDebut)->subDay()->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            Solde initial
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 text-right text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 text-right text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 text-right text-sm font-medium {{ $soldeInitial >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($soldeInitial, 0, ',', ' ') }}
                        </td>
                    </tr>
                    
                    @forelse($ecritures as $ecriture)
                        @php
                            if ($compte->type === 'actif') {
                                $soldeProgressif += $ecriture->debit - $ecriture->credit;
                            } else {
                                $soldeProgressif += $ecriture->credit - $ecriture->debit;
                            }
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($ecriture->journal->date_ecriture)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div>{{ $ecriture->libelle_ecriture ?: $ecriture->journal->libelle }}</div>
                                @if($ecriture->client)
                                    <div class="text-xs text-gray-500">Client: {{ $ecriture->client->nom }}</div>
                                @endif
                                @if($ecriture->produit)
                                    <div class="text-xs text-gray-500">Produit: {{ $ecriture->produit->nom }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $ecriture->journal->reference ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm {{ $ecriture->debit > 0 ? 'font-medium text-red-600' : 'text-gray-400' }}">
                                {{ $ecriture->debit > 0 ? number_format($ecriture->debit, 0, ',', ' ') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm {{ $ecriture->credit > 0 ? 'font-medium text-green-600' : 'text-gray-400' }}">
                                {{ $ecriture->credit > 0 ? number_format($ecriture->credit, 0, ',', ' ') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ $soldeProgressif >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($soldeProgressif, 0, ',', ' ') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-file-alt text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg">Aucun mouvement sur cette période</p>
                                <p class="text-sm">Le compte n'a pas été utilisé</p>
                            </td>
                        </tr>
                    @endforelse
                    
                    <!-- Ligne totals -->
                    @if($ecritures->count() > 0)
                        <tr class="bg-gray-100 font-medium">
                            <td colspan="3" class="px-6 py-4 text-sm text-gray-900">TOTAUX</td>
                            <td class="px-6 py-4 text-right text-sm text-red-600">
                                {{ number_format($debitTotal, 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-green-600">
                                {{ number_format($creditTotal, 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm {{ $soldeFinal >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($soldeFinal, 0, ',', ' ') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Actions -->
        <div class="bg-gray-50 px-6 py-4 border-t">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    {{ $ecritures->count() }} écriture(s) trouvée(s)
                </div>
                <div class="flex space-x-2">
                    <button onclick="window.print()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-print mr-2"></i>Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
