@extends('layouts.app')

@section('title', 'Compte de Résultat')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Compte de Résultat</h1>
                <p class="text-gray-600 mt-2">Analyse des charges et produits de l'exercice</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('comptabilite.classes-comptables.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    ← Retour aux classes
                </a>
                <button onclick="window.print()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    🖨️ Imprimer
                </button>
            </div>
        </div>
    </div>

    <!-- Résumé -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-red-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Charges (Classe 6)</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($totalCharges, 2) }} €</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Produits (Classe 7)</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($totalProduits, 2) }} €</p>
                </div>
            </div>
        </div>

        <div class="bg-{{ $resultat >= 0 ? 'blue' : 'orange' }}-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-{{ $resultat >= 0 ? 'blue' : 'orange' }}-100 text-{{ $resultat >= 0 ? 'blue' : 'orange' }}-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">
                        {{ $resultat >= 0 ? 'Bénéfice' : 'Perte' }}
                    </p>
                    <p class="text-2xl font-bold text-{{ $resultat >= 0 ? 'blue' : 'orange' }}-600">
                        {{ number_format(abs($resultat), 2) }} €
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau détaillé du compte de résultat -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Détail du Compte de Résultat</h2>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
            <!-- Colonne CHARGES (Classe 6) -->
            <div class="border-r border-gray-200">
                <div class="bg-red-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-red-700">CHARGES (Classe 6)</h3>
                </div>
                
                @if($charges->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach($charges as $compte)
                            <div class="px-6 py-4 flex justify-between items-center hover:bg-gray-50">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $compte->nom }}</div>
                                    <div class="text-sm text-gray-500 font-mono">{{ $compte->numero }}</div>
                                </div>
                                <div class="text-right font-mono text-red-600">
                                    {{ number_format(abs($compte->solde_actuel), 2) }} €
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="bg-red-100 px-6 py-4 border-t-2 border-red-200">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-red-800">Total des Charges</div>
                            <div class="font-bold text-lg text-red-800 font-mono">
                                {{ number_format($totalCharges, 2) }} €
                            </div>
                        </div>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">
                        <div class="mx-auto w-12 h-12 text-gray-400 mb-4">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p>Aucun compte de charge trouvé</p>
                    </div>
                @endif
            </div>

            <!-- Colonne PRODUITS (Classe 7) -->
            <div>
                <div class="bg-green-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-green-700">PRODUITS (Classe 7)</h3>
                </div>
                
                @if($produits->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach($produits as $compte)
                            <div class="px-6 py-4 flex justify-between items-center hover:bg-gray-50">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $compte->nom }}</div>
                                    <div class="text-sm text-gray-500 font-mono">{{ $compte->numero }}</div>
                                </div>
                                <div class="text-right font-mono text-green-600">
                                    {{ number_format(abs($compte->solde_actuel), 2) }} €
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="bg-green-100 px-6 py-4 border-t-2 border-green-200">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-green-800">Total des Produits</div>
                            <div class="font-bold text-lg text-green-800 font-mono">
                                {{ number_format($totalProduits, 2) }} €
                            </div>
                        </div>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">
                        <div class="mx-auto w-12 h-12 text-gray-400 mb-4">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p>Aucun compte de produit trouvé</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Résultat final -->
        <div class="bg-{{ $resultat >= 0 ? 'blue' : 'orange' }}-100 px-6 py-6 border-t-2 border-{{ $resultat >= 0 ? 'blue' : 'orange' }}-200">
            <div class="flex justify-between items-center">
                <div class="font-bold text-xl text-{{ $resultat >= 0 ? 'blue' : 'orange' }}-800">
                    {{ $resultat >= 0 ? 'BÉNÉFICE DE L\'EXERCICE' : 'PERTE DE L\'EXERCICE' }}
                </div>
                <div class="font-bold text-2xl text-{{ $resultat >= 0 ? 'blue' : 'orange' }}-800 font-mono">
                    {{ number_format(abs($resultat), 2) }} €
                </div>
            </div>
            @if($resultat > 0)
                <p class="text-sm text-blue-700 mt-2">
                    💰 L'entreprise a réalisé un bénéfice de {{ number_format($resultat, 2) }} € sur la période.
                </p>
            @elseif($resultat < 0)
                <p class="text-sm text-orange-700 mt-2">
                    ⚠️ L'entreprise a subi une perte de {{ number_format(abs($resultat), 2) }} € sur la période.
                </p>
            @else
                <p class="text-sm text-gray-700 mt-2">
                    ⚖️ L'entreprise est à l'équilibre (aucun bénéfice ni perte).
                </p>
            @endif
        </div>
    </div>

    <!-- Note explicative -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">À propos du Compte de Résultat</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Le compte de résultat présente l'ensemble des charges (classe 6) et des produits (classe 7) de l'exercice. 
                    La différence entre ces deux montants détermine le résultat de l'entreprise : 
                    bénéfice si les produits sont supérieurs aux charges, perte dans le cas contraire.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        body { font-size: 12px; }
        .container { max-width: none; margin: 0; padding: 0; }
    }
</style>
@endsection
