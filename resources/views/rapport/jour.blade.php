@extends('layouts.appvente')
@section('content')

<div class="max-w-7xl mx-auto px-6 py-6">
    <!-- Messages de statut -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-700 rounded-xl shadow-sm text-center font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl shadow-sm text-center font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Zone consolidée : Titre, Filtre et Export -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <!-- Titre et informations principales -->
        <div class="mb-6 text-center border-b border-gray-200 pb-4">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                Rapport Journalier
            </h1>
            <div class="flex justify-center items-center gap-6 text-gray-600">
                <span>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                <span class="text-gray-700 font-medium">Comptoriste : {{ Auth::user()->name ?? 'John' }}</span>
            </div>
        </div>

        <!-- Ligne des contrôles : Filtre date et Export -->
        <div class="flex flex-wrap gap-4 items-end justify-between">
            <!-- Filtre par date -->
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date :</label>
                    <input type="date" name="date" value="{{ $date }}" 
                           class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           onchange="this.form.submit()">
                </div>
            </form>
            
            <!-- Export PDF -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">&nbsp;</label>
                <a href="{{ route('rapport.export_pdf', ['pointDeVenteId' => request()->route('pointDeVenteId'), 'date' => $date]) }}" 
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

    <!-- Tableau moderne du rapport -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <!-- Résumé financier principal -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Recettes -->
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-600 mb-1">Total Recettes</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format($totalRecettes, 0, ',', ' ') }} F</div>
                    <div class="text-xs text-gray-500 mt-1">Ventes + Créances + Entrées</div>
                </div>
                
                <!-- Créances en cours -->
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-600 mb-1">Créances en cours</div>
                    <div class="text-2xl font-bold text-orange-600">{{ number_format($totalCreance, 0, ',', ' ') }} F</div>
                    <div class="text-xs text-gray-500 mt-1">À recouvrer</div>
                </div>
                
                <!-- Dépenses -->
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-600 mb-1">Total Dépenses</div>
                    <div class="text-2xl font-bold text-red-600">{{ number_format($depenses, 0, ',', ' ') }} F</div>
                    <div class="text-xs text-gray-500 mt-1">Sorties de caisse</div>
                </div>
                
                <!-- Solde -->
                <div class="text-center bg-blue-100 rounded-lg p-4">
                    <div class="text-sm font-medium text-blue-700 mb-1">Solde Final</div>
                    <div class="text-3xl font-extrabold text-blue-900">{{ number_format($solde, 0, ',', ' ') }} F</div>
                    <div class="text-xs text-blue-600 mt-1">Recettes - Créances - Dépenses</div>
                </div>
            </div>
        </div>

        <!-- Détail des RECETTES -->
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-bold text-green-700 mb-4">💰 Détail des Recettes</h3>
            
            <!-- Tableau des types de recettes -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- 1. Ventes -->
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <h4 class="font-bold text-green-700 mb-3">🛒 Ventes du jour</h4>
                    <div class="text-2xl font-bold text-green-600 mb-2">{{ number_format($recettesVentes, 0, ',', ' ') }} F</div>
                    
                    @if($ventesParMode->isNotEmpty())
                        <div class="space-y-1">
                            @foreach($ventesParMode as $mode => $data)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ $data['mode'] }} ({{ $data['count'] }})</span>
                                    <span class="font-medium">{{ number_format($data['total'], 0, ',', ' ') }} F</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <!-- 2. Paiements créances -->
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <h4 class="font-bold text-blue-700 mb-3">🏦 Paiements créances</h4>
                    <div class="text-2xl font-bold text-blue-600 mb-2">{{ number_format($recettesPaiementsCreances, 0, ',', ' ') }} F</div>
                    
                    @if($paiementsCreances->isNotEmpty())
                        <div class="space-y-1 max-h-20 overflow-y-auto">
                            @foreach($paiementsCreances as $paiement)
                                <div class="text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($paiement->created_at)->format('H:i') }} - {{ number_format($paiement->montant, 0, ',', ' ') }} F
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-gray-500 italic">Aucun paiement de créance</div>
                    @endif
                </div>
                
                <!-- 3. Entrées diverses -->
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <h4 class="font-bold text-purple-700 mb-3">📥 Entrées diverses</h4>
                    <div class="text-2xl font-bold text-purple-600 mb-2">{{ number_format($recettesEntreesDiverses, 0, ',', ' ') }} F</div>
                    
                    @if($entresDiverses->isNotEmpty())
                        <div class="space-y-1 max-h-20 overflow-y-auto">
                            @foreach($entresDiverses as $entree)
                                <div class="text-sm text-gray-600">
                                    {{ substr($entree->libele, 0, 20) }}{{ strlen($entree->libele) > 20 ? '...' : '' }} - {{ number_format($entree->montant, 0, ',', ' ') }} F
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-gray-500 italic">Aucune entrée diverse</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Détails des créances -->
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-bold text-orange-700 mb-4">Détail des Créances</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-orange-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Client</th>
                            <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">Serveuses</th>
                            <th class="px-4 py-3 text-right text-sm font-bold text-gray-700">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($detailsCreance as $detail)
                            <tr class="hover:bg-orange-25 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $detail['client'] }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-center gap-1">
                                        @foreach($detail['serveuses'] as $serv)
                                            <span class="inline-block bg-blue-100 text-blue-800 rounded-full px-2 py-1 text-xs font-medium">
                                                {{ $serv }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-orange-600">
                                    {{ number_format($detail['total'], 0, ',', ' ') }} F
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500 italic">
                                    Aucune créance ce jour
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Détails des dépenses -->
        <div class="p-6">
            <h3 class="text-lg font-bold text-red-700 mb-4">Détail des Dépenses</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Compte</th>
                            <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Libellé</th>
                            <th class="px-4 py-3 text-right text-sm font-bold text-gray-700">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $depensesList = \App\Models\EntreeSortie::whereDate('created_at', $date)
                                ->where('point_de_vente_id', request()->route('pointDeVenteId'))
                                ->where('type', 'sortie')
                                ->get();
                        @endphp
                        @forelse($depensesList as $dep)
                            <tr class="hover:bg-red-25 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $dep->compte->nom ?? '' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $dep->libele ?? $dep->motif ?? '' }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-red-600">
                                    -{{ number_format($dep->montant, 0, ',', ' ') }} F
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500 italic">
                                    Aucune dépense ce jour
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="mt-6 flex justify-center gap-4">
        <a href="{{ url()->previous() }}" 
           class="inline-flex items-center px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 shadow transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Retour
        </a>
    </div>
</div>
@endsection
