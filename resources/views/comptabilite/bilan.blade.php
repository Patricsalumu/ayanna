@extends('layouts.appsalle')

@section('title', 'Bilan Comptable')

@section('content')
@include('comptabilite.partials.nav')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Bilan Comptable</h1>
                    <p class="text-blue-100">Situation patrimoniale au {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('comptabilite.bilan.export-pdf', ['date' => $date]) }}" 
                       class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
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
                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Actualiser
                </button>
            </form>
        </div>

        <!-- Tableau du bilan -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
            <!-- ACTIF -->
            <div class="border-r border-gray-200">
                <div class="bg-blue-50 px-6 py-3 border-b">
                    <h2 class="text-lg font-bold text-blue-900">ACTIF</h2>
                </div>
                <div class="p-6">
                    @if($actifs->count() > 0)
                        <div class="space-y-3">
                            @foreach($actifs as $compte)
                                @if($compte->solde_bilan > 0)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $compte->numero }}</div>
                                            <div class="text-sm text-gray-600">{{ $compte->nom }}</div>
                                        </div>
                                        <div class="font-semibold text-blue-600">
                                            {{ number_format($compte->solde_bilan, 0, ',', ' ') }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            
                            <!-- Total Actif -->
                            <div class="flex justify-between items-center py-3 border-t-2 border-blue-200 bg-blue-50 rounded-lg px-4 mt-4">
                                <div class="font-bold text-blue-900">TOTAL ACTIF</div>
                                <div class="font-bold text-xl text-blue-600">
                                    {{ number_format($totalActif, 0, ',', ' ') }} FC
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-chart-bar text-3xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Aucun compte d'actif</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- PASSIF -->
            <div>
                <div class="bg-purple-50 px-6 py-3 border-b">
                    <h2 class="text-lg font-bold text-purple-900">PASSIF</h2>
                </div>
                <div class="p-6">
                    @if($passifs->count() > 0)
                        <div class="space-y-3">
                            @foreach($passifs as $compte)
                                @if($compte->solde_bilan > 0)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $compte->numero }}</div>
                                            <div class="text-sm text-gray-600">{{ $compte->nom }}</div>
                                        </div>
                                        <div class="font-semibold text-purple-600">
                                            {{ number_format($compte->solde_bilan, 0, ',', ' ') }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            
                            <!-- Résultat de l'exercice -->
                            @if(isset($resultatExercice) && $resultatExercice != 0)
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 bg-yellow-50">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ $resultatExercice > 0 ? 'Résultat bénéficiaire' : 'Résultat déficitaire' }}
                                        </div>
                                        <div class="text-sm text-gray-600">Exercice en cours</div>
                                    </div>
                                    <div class="font-semibold {{ $resultatExercice > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format(abs($resultatExercice), 0, ',', ' ') }}
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Total Passif -->
                            <div class="flex justify-between items-center py-3 border-t-2 border-purple-200 bg-purple-50 rounded-lg px-4 mt-4">
                                <div class="font-bold text-purple-900">TOTAL PASSIF</div>
                                <div class="font-bold text-xl text-purple-600">
                                    {{ number_format($totalPassif, 0, ',', ' ') }} FC
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-chart-pie text-3xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Aucun compte de passif</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Résumé et équilibre -->
        <div class="bg-gray-50 px-6 py-4 border-t">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600">Total Actif</div>
                    <div class="text-xl font-bold text-blue-600">{{ number_format($totalActif, 0, ',', ' ') }} FC</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600">Total Passif</div>
                    <div class="text-xl font-bold text-purple-600">{{ number_format($totalPassif, 0, ',', ' ') }} FC</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600">Équilibre</div>
                    @php $equilibre = $totalActif - $totalPassif; @endphp
                    <div class="text-xl font-bold {{ abs($equilibre) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                        @if(abs($equilibre) < 0.01)
                            <i class="fas fa-check-circle mr-2"></i>Équilibré
                        @else
                            <i class="fas fa-exclamation-triangle mr-2"></i>{{ number_format(abs($equilibre), 0, ',', ' ') }} FC
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations complémentaires -->
        <div class="bg-blue-50 px-6 py-4 border-t">
            <div class="text-center">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Bilan simplifié généré automatiquement - Date d'arrêté : {{ \Carbon\Carbon::parse($date)->format('d/m/Y à H:i') }}
                </p>
                <p class="text-xs text-blue-600 mt-1">
                    Les montants sont exprimés en FC. Seuls les comptes avec un solde non nul sont affichés.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
