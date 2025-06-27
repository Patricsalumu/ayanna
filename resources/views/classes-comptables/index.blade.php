@extends('layouts.appsalle')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- En-tête -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Plan Comptable</h1>
            <p class="text-gray-600 mt-2">Gestion des classes comptables selon le plan comptable général</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('comptabilite.plan-comptable.bilan') }}" 
               class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow">
                📊 Bilan
            </a>
            <a href="{{ route('comptabilite.plan-comptable.compte-resultat') }}" 
               class="inline-flex items-center px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 shadow">
                📈 Compte de résultat
            </a>
            <a href="{{ route('comptes.index') }}" 
               class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700 shadow">
                🏦 Gérer les comptes
            </a>
        </div>
    </div>

    <!-- Classes principales (1-7) -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Classes Principales</h2>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($classesPrincipales as $classe)
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg
                                {{ $classe->type_nature === 'charge' ? 'bg-red-500' : 
                                   ($classe->type_nature === 'produit' ? 'bg-green-500' : 
                                   ($classe->type_nature === 'actif' ? 'bg-blue-500' : 'bg-purple-500')) }}">
                                {{ $classe->numero }}
                            </div>
                            <div class="ml-3">
                                <h3 class="font-semibold text-gray-900">{{ $classe->nom }}</h3>
                                <p class="text-sm text-gray-500">{{ ucfirst($classe->type_nature) }}</p>
                            </div>
                        </div>
                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm">
                            {{ $classe->comptes_count ?? 0 }} comptes
                        </span>
                    </div>
                    
                    <p class="text-gray-600 text-sm mb-4">{{ $classe->description }}</p>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-xs px-2 py-1 rounded-full
                            {{ $classe->type_document === 'bilan' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                            {{ ucfirst($classe->type_document) }}
                        </span>
                        <a href="{{ route('comptabilite.classes-comptables.show', $classe) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Voir détails →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Sous-classes -->
    @if($sousClasses->count() > 0)
    <div>
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Sous-classes</h2>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comptes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($sousClasses->sortBy('ordre_affichage') as $sousClasse)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $sousClasse->type_nature === 'charge' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $sousClasse->numero }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $sousClasse->nom }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($sousClasse->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Classe {{ $sousClasse->classe_parent }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($sousClasse->type_nature) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $sousClasse->comptes_count ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('comptabilite.classes-comptables.show', $sousClasse) }}" 
                               class="text-blue-600 hover:text-blue-900">Voir</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
