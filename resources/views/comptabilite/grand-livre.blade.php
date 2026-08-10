@extends('layouts.appsalle')

@section('title', 'Grand Livre')

@section('content')
@include('comptabilite.partials.nav')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ search: '{{ $search ?? '' }}' }">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Grand Livre</h1>
                    <p class="text-green-100">Mouvements détaillés par compte</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('comptabilite.grand-livre.export-general-pdf', request()->query()) }}" 
                       class="bg-white text-green-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF Général
                    </a>
                    <a href="{{ route('comptabilite.journal') }}" 
                       class="bg-white text-green-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Retour au journal
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtres de recherche -->
        <div class="bg-gray-50 px-6 py-4 border-b">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-72">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recherche compte</label>
                    <input type="search" name="search" x-model.debounce.250ms="search"
                           placeholder="Rechercher un compte par numéro ou nom"
                           class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Rechercher
                </button>
            </form>
        </div>

        <!-- Liste des comptes -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($comptes as $compte)
                    @php
                        // Calculer les totaux pour la période (écritures non annulées)
                        $debitTotal = $compte->ecritures()->whereHas('journal', function($q) use ($dateDebut, $dateFin) {
                            $q->whereBetween('date_ecriture', [$dateDebut, $dateFin])
                              ->where('statut', '!=', 'annule');
                        })->sum('debit');
                        
                        $creditTotal = $compte->ecritures()->whereHas('journal', function($q) use ($dateDebut, $dateFin) {
                            $q->whereBetween('date_ecriture', [$dateDebut, $dateFin])
                              ->where('statut', '!=', 'annule');
                        })->sum('credit');

                        $classeNum = intval($compte->classeComptable->numero ?? 0);
                        $isDebiteurClass = in_array($classeNum, [2,3,4,5,6]);
                        $isCrediteurClass = in_array($classeNum, [1,7]);

                        if ($isDebiteurClass) {
                            $solde = $compte->solde_initial + $debitTotal - $creditTotal; // débit - crédit
                            $debitClass = 'text-green-600';
                            $creditClass = 'text-red-600';
                        } elseif ($isCrediteurClass) {
                            $solde = $compte->solde_initial + $creditTotal - $debitTotal; // crédit - débit
                            $debitClass = 'text-red-600';
                            $creditClass = 'text-green-600';
                        } else {
                            $solde = $compte->solde_initial + $debitTotal - $creditTotal;
                            $debitClass = 'text-green-600';
                            $creditClass = 'text-red-600';
                        }

                        $mouvements = $debitTotal + $creditTotal;
                        $soldeColor = $solde >= 0 ? 'text-green-600' : 'text-red-600';
                    @endphp
                    
                    <div x-show="search.trim() === '' || ('{{ strtolower($compte->numero . ' ' . $compte->nom) }}').includes(search.toLowerCase())"
                         class="bg-white border rounded-lg p-4 hover:shadow-md transition-shadow {{ $mouvements > 0 ? 'border-green-200' : 'border-gray-200' }}">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $compte->numero }}</h3>
                                <p class="text-sm text-gray-600">{{ $compte->nom }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $compte->type === 'actif' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($compte->type) }}
                            </span>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Débit période:</span>
                                <span class="font-medium {{ $debitClass }}">@currency($debitTotal)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Crédit période:</span>
                                <span class="font-medium {{ $creditClass }}">@currency($creditTotal)</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-gray-900 font-medium">Solde actuel:</span>
                                <span class="font-bold {{ $soldeColor }}">@currency($solde)</span>
                            </div>
                        </div>

                        @if($mouvements > 0)
                            <div class="mt-4">
                                <a href="{{ route('comptabilite.grand-livre', ['compteId' => $compte->id, 'date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}"
                                   class="w-full bg-green-600 text-white text-center py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors block">
                                    <i class="fas fa-list mr-2"></i>Voir les détails
                                </a>
                            </div>
                        @else
                            <div class="mt-4">
                                <span class="w-full bg-gray-100 text-gray-500 text-center py-2 rounded-lg text-sm block">
                                    Aucun mouvement
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            @if($comptes->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-book text-4xl text-gray-300 mb-4"></i>
                    <p class="text-lg text-gray-500">Aucun compte trouvé</p>
                    <p class="text-sm text-gray-400">Configurez d'abord vos comptes comptables</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
