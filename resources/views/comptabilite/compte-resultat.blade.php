@extends('layouts.appsalle')

@section('title', 'Compte de Résultat')

@section('content')
@include('comptabilite.partials.nav')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-green-600 to-blue-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Compte de Résultat</h1>
                    <p class="text-green-100">Performance de l'entreprise du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
                </div>
                <div class="flex space-x-2">
                    <button onclick="window.print()" class="bg-white text-green-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-print mr-2"></i>Imprimer
                    </button>
                </div>
            </div>
        </div>

        <!-- Filtres de période -->
        <div class="bg-gray-50 px-6 py-4 border-b">
            <form method="GET" class="flex gap-4 items-end">
                <div class="flex-1 max-w-xs">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                    <input type="date" name="date_debut" value="{{ $dateDebut }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                </div>
                <div class="flex-1 max-w-xs">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_fin" value="{{ $dateFin }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Actualiser
                </button>
            </form>
        </div>

        <!-- Tableau du compte de résultat -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
            <!-- CHARGES -->
            <div class="border-r border-gray-200">
                <div class="bg-red-50 px-6 py-3 border-b">
                    <h2 class="text-lg font-bold text-red-900">CHARGES (Classe 6)</h2>
                </div>
                <div class="p-6">
                    @if($charges->count() > 0)
                        <div class="space-y-3">
                            @foreach($charges as $compte)
                                @if($compte->montant > 0)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $compte->numero }}</div>
                                            <div class="text-sm text-gray-600">{{ $compte->nom }}</div>
                                        </div>
                                        <div class="font-semibold text-red-600">
                                            {{ number_format($compte->montant, 0, ',', ' ') }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            
                            <!-- Total Charges -->
                            <div class="flex justify-between items-center py-3 border-t-2 border-red-200 bg-red-50 rounded-lg px-4 mt-4">
                                <div class="font-bold text-red-900">TOTAL CHARGES</div>
                                <div class="font-bold text-xl text-red-600">
                                    {{ number_format($totalCharges, 0, ',', ' ') }} FC
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-receipt text-3xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Aucune charge enregistrée</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- PRODUITS -->
            <div>
                <div class="bg-green-50 px-6 py-3 border-b">
                    <h2 class="text-lg font-bold text-green-900">PRODUITS (Classe 7)</h2>
                </div>
                <div class="p-6">
                    @if($produits->count() > 0)
                        <div class="space-y-3">
                            @foreach($produits as $compte)
                                @if($compte->montant > 0)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $compte->numero }}</div>
                                            <div class="text-sm text-gray-600">{{ $compte->nom }}</div>
                                        </div>
                                        <div class="font-semibold text-green-600">
                                            {{ number_format($compte->montant, 0, ',', ' ') }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            
                            <!-- Total Produits -->
                            <div class="flex justify-between items-center py-3 border-t-2 border-green-200 bg-green-50 rounded-lg px-4 mt-4">
                                <div class="font-bold text-green-900">TOTAL PRODUITS</div>
                                <div class="font-bold text-xl text-green-600">
                                    {{ number_format($totalProduits, 0, ',', ' ') }} FC
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-chart-line text-3xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Aucun produit enregistré</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Résultat -->
        <div class="bg-gray-50 px-6 py-6 border-t">
            <div class="max-w-md mx-auto">
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">RÉSULTAT DE L'EXERCICE</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Produits :</span>
                            <span class="font-medium text-green-600">{{ number_format($totalProduits, 0, ',', ' ') }} FC</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Charges :</span>
                            <span class="font-medium text-red-600">{{ number_format($totalCharges, 0, ',', ' ') }} FC</span>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900">Résultat Net :</span>
                                <span class="font-bold text-2xl {{ $resultat >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $resultat >= 0 ? '+' : '' }}{{ number_format($resultat, 0, ',', ' ') }} FC
                                </span>
                            </div>
                            <div class="mt-2">
                                @if($resultat > 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-arrow-up mr-2"></i>Bénéfice
                                    </span>
                                @elseif($resultat < 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-arrow-down mr-2"></i>Perte
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-minus mr-2"></i>Équilibre
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analyses complémentaires -->
        @if($totalProduits > 0)
            <div class="bg-blue-50 px-6 py-4 border-t">
                <h4 class="font-medium text-blue-900 mb-3">Analyses</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="bg-white p-3 rounded-lg">
                        <div class="text-gray-600">Taux de marge</div>
                        <div class="font-semibold text-blue-600">
                            {{ number_format(($resultat / $totalProduits) * 100, 1) }}%
                        </div>
                    </div>
                    <div class="bg-white p-3 rounded-lg">
                        <div class="text-gray-600">Ratio charges/produits</div>
                        <div class="font-semibold text-blue-600">
                            {{ number_format(($totalCharges / $totalProduits) * 100, 1) }}%
                        </div>
                    </div>
                    <div class="bg-white p-3 rounded-lg">
                        <div class="text-gray-600">Période d'analyse</div>
                        <div class="font-semibold text-blue-600">
                            {{ \Carbon\Carbon::parse($dateDebut)->diffInDays(\Carbon\Carbon::parse($dateFin)) + 1 }} jour(s)
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Informations complémentaires -->
        <div class="bg-gray-100 px-6 py-4 border-t">
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-2"></i>
                    Compte de résultat généré automatiquement - Période du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Les montants sont exprimés en FC. Seuls les comptes avec des mouvements sont affichés.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
