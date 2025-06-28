@extends('layouts.appsalle')

@section('title', 'Classe ' . $classeComptable->numero . ' - ' . $classeComptable->nom)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Classe {{ $classeComptable->numero }} - {{ $classeComptable->nom }}
                </h1>
                @if($classeComptable->description)
                    <p class="text-gray-600 mt-2">{{ $classeComptable->description }}</p>
                @endif
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('classes-comptables.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    ← Retour aux classes
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques de la classe -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-blue-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Comptes dans cette classe</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $comptes->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Solde total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($soldeTotal, 2) }} €</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Type de classe</p>
                    <p class="text-2xl font-bold text-gray-900">
                        @if(in_array($classeComptable->numero, [1, 2, 3, 4, 5]))
                            Bilan
                        @else
                            Gestion
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des comptes -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Comptes de la classe {{ $classeComptable->numero }}</h2>
        </div>
        
        @if($comptes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Numéro
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nom
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Solde actuel
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($comptes as $compte)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                    {{ $compte->numero }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $compte->nom }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($compte->type === 'actif') bg-green-100 text-green-800
                                        @elseif($compte->type === 'passif') bg-blue-100 text-blue-800
                                        @elseif($compte->type === 'charge') bg-red-100 text-red-800
                                        @elseif($compte->type === 'produit') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($compte->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono
                                    @if($compte->solde_actuel > 0) text-green-600
                                    @elseif($compte->solde_actuel < 0) text-red-600
                                    @else text-gray-900
                                    @endif">
                                    {{ number_format($compte->solde_actuel, 2) }} €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('comptes.edit', $compte) }}" 
                                           class="text-blue-600 hover:text-blue-900 font-medium">
                                            Modifier
                                        </a>
                                        <a href="{{ route('comptes.mouvements', $compte) }}" 
                                           class="text-green-600 hover:text-green-900 font-medium">
                                            Mouvements
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center">
                <div class="mx-auto w-12 h-12 text-gray-400 mb-4">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-gray-500 text-sm">Aucun compte n'a encore été créé pour cette classe.</p>
                <a href="{{ route('comptes.create') }}" 
                   class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Créer un compte
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
