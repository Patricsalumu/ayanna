@extends('layouts.appsalle')

@section('title', 'Balance Comptable')

@section('content')
@include('comptabilite.partials.nav')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Balance Comptable</h1>
                    <p class="text-indigo-100">État des soldes au {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('comptabilite.balance.export-pdf', ['date' => $date]) }}" 
                       class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtre de date -->
        <div class="bg-gray-50 px-6 py-4 border-b">
            <form method="GET" class="flex gap-4 items-end">
                <div class="flex-1 max-w-xs">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date d'arrêté</label>
                    <input type="date" name="date" value="{{ $date }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Actualiser
                </button>
            </form>
        </div>

        <!-- Résumé -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600">Total Débit</div>
                    <div class="text-2xl font-bold text-red-600">{{ number_format($totalDebit, 0, ',', ' ') }} FC</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600">Total Crédit</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format($totalCredit, 0, ',', ' ') }} FC</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600">Équilibre</div>
                    @php $equilibre = $totalDebit - $totalCredit; @endphp
                    <div class="text-2xl font-bold {{ abs($equilibre) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                        {{ abs($equilibre) < 0.01 ? '✓ Équilibré' : '⚠ ' . number_format(abs($equilibre), 0, ',', ' ') . ' FC' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Table de la balance -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Compte</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Débit Période</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Crédit Période</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Solde Débit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Solde Crédit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($balance as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ $item['compte']->numero }}</div>
                                <div class="text-gray-600">{{ $item['compte']->nom }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $item['compte']->type === 'actif' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst($item['compte']->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm {{ $item['debit_periode'] > 0 ? 'font-medium text-red-600' : 'text-gray-400' }}">
                                {{ $item['debit_periode'] > 0 ? number_format($item['debit_periode'], 0, ',', ' ') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm {{ $item['credit_periode'] > 0 ? 'font-medium text-green-600' : 'text-gray-400' }}">
                                {{ $item['credit_periode'] > 0 ? number_format($item['credit_periode'], 0, ',', ' ') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm {{ $item['solde_debit'] > 0 ? 'font-bold text-red-600' : 'text-gray-400' }}">
                                {{ $item['solde_debit'] > 0 ? number_format($item['solde_debit'], 0, ',', ' ') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm {{ $item['solde_credit'] > 0 ? 'font-bold text-green-600' : 'text-gray-400' }}">
                                {{ $item['solde_credit'] > 0 ? number_format($item['solde_credit'], 0, ',', ' ') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-balance-scale text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg">Aucun compte trouvé</p>
                                <p class="text-sm">Configurez d'abord vos comptes comptables</p>
                            </td>
                        </tr>
                    @endforelse
                    
                    <!-- Ligne des totaux -->
                    @if(count($balance) > 0)
                        <tr class="bg-gray-100 font-bold border-t-2 border-gray-300">
                            <td colspan="2" class="px-6 py-4 text-sm text-gray-900">TOTAUX</td>
                            <td class="px-6 py-4 text-right text-sm text-red-600">
                                {{ number_format(collect($balance)->sum('debit_periode'), 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-green-600">
                                {{ number_format(collect($balance)->sum('credit_periode'), 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-red-600">
                                {{ number_format($totalDebit, 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-green-600">
                                {{ number_format($totalCredit, 0, ',', ' ') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pied de page -->
        <div class="bg-gray-50 px-6 py-4 border-t">
            <div class="text-center">
                <div class="text-sm text-gray-600">
                    {{ count($balance) }} compte(s) - Balance au {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
