@extends('layouts.appsalle')

@section('title', 'Grand Livre')

@section('content')
@include('comptabilite.partials.nav')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Grand Livre</h1>
                    <p class="text-green-100">Mouvements détaillés par compte</p>
                </div>
                <a href="{{ route('comptabilite.journal') }}" 
                   class="bg-white text-green-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Retour au journal
                </a>
            </div>
        </div>

        <!-- Filtres de période -->
        <div class="bg-gray-50 px-6 py-4 border-b">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                    <input type="date" name="date_debut" value="{{ $dateDebut }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_fin" value="{{ $dateFin }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Actualiser
                </button>
            </form>
        </div>

        <!-- Liste des comptes -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($comptes as $compte)
                    @php
                        // Calculer le solde pour la période
                        $debitTotal = $compte->ecritures()->whereHas('journal', function($q) use ($dateDebut, $dateFin) {
                            $q->whereBetween('date_ecriture', [$dateDebut, $dateFin]);
                        })->sum('debit');
                        
                        $creditTotal = $compte->ecritures()->whereHas('journal', function($q) use ($dateDebut, $dateFin) {
                            $q->whereBetween('date_ecriture', [$dateDebut, $dateFin]);
                        })->sum('credit');
                        
                        $mouvements = $debitTotal + $creditTotal;
                        
                        if ($compte->type === 'actif') {
                            $solde = $compte->solde_initial + $debitTotal - $creditTotal;
                        } else {
                            $solde = $compte->solde_initial + $creditTotal - $debitTotal;
                        }
                        
                        $soldeColor = $solde >= 0 ? 'text-green-600' : 'text-red-600';
                    @endphp
                    
                    <div class="bg-white border rounded-lg p-4 hover:shadow-md transition-shadow {{ $mouvements > 0 ? 'border-green-200' : 'border-gray-200' }}">
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
                                <span class="font-medium text-red-600">{{ number_format($debitTotal, 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Crédit période:</span>
                                <span class="font-medium text-green-600">{{ number_format($creditTotal, 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-gray-900 font-medium">Solde actuel:</span>
                                <span class="font-bold {{ $soldeColor }}">{{ number_format($solde, 0, ',', ' ') }}</span>
                            </div>
                        </div>
                        
                        @if($mouvements > 0)
                            <div class="mt-4">
                                <a href="{{ route('comptabilite.grand-livre', $compte->id) }}?date_debut={{ $dateDebut }}&date_fin={{ $dateFin }}" 
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
