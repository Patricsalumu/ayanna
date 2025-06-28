@extends('layouts.appsalle')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-6">
    <!-- Barre de contrôle -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <!-- Bouton retour à gauche -->
        <div class="flex gap-2 order-1 sm:order-none">
            <a href="javascript:history.back()" class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Retour
            </a>
        </div>
        
        <!-- Recherche au centre -->
        <div class="flex-1 flex justify-center order-3 sm:order-none">
            <div class="relative w-full max-w-xs">
                <input id="searchInput" type="text" placeholder="Rechercher par nom, numéro, type..." class="w-full rounded-full border border-gray-300 pl-4 pr-10 py-2 focus:outline-none focus:border-gray-400 shadow-sm" />
                <span class="absolute right-3 top-2.5 text-gray-400">🔍</span>
            </div>
        </div>
        
        <!-- Titre à droite -->
        <div class="order-2 sm:order-none">
            <h1 class="text-2xl font-bold text-gray-900">Plan Comptable</h1>
        </div>
    </div>

    <!-- Sous-titre -->
    <div class="mb-6 text-center">
        <p class="text-gray-600">Gestion des classes comptables selon le plan comptable général</p>
    </div>

    <!-- Classes principales (1-7) -->
    <div class="mb-8">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" id="classesPrincipales">
            @foreach($classesPrincipales as $classe)
                <div class="bg-white rounded-xl shadow hover:shadow-lg transition p-6 classe-card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg
                                {{ $classe->type_nature === 'charge' ? 'bg-red-500' : 
                                   ($classe->type_nature === 'produit' ? 'bg-green-500' : 
                                   ($classe->type_nature === 'actif' ? 'bg-blue-500' : 'bg-purple-500')) }}">
                                {{ $classe->numero }}
                            </div>
                            <div class="ml-3">
                                <h3 class="font-semibold text-gray-900 classe-nom">{{ $classe->nom }}</h3>
                                <p class="text-sm text-gray-500 classe-type">{{ ucfirst($classe->type_nature) }}</p>
                            </div>
                        </div>
                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm">
                            {{ $classe->comptes_count ?? 0 }} comptes
                        </span>
                    </div>
                    
                    <p class="text-gray-600 text-sm mb-4 classe-description">{{ $classe->description }}</p>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-xs px-2 py-1 rounded-full
                            {{ $classe->type_document === 'bilan' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                            {{ ucfirst($classe->type_document) }}
                        </span>
                        <a href="{{ route('classes-comptables.show', $classe) }}" 
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
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200" id="sousClassesTable">
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
                    <tr class="hover:bg-gray-50 sous-classe-row">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium sous-classe-numero
                                {{ $sousClasse->type_nature === 'charge' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $sousClasse->numero }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 sous-classe-nom">{{ $sousClasse->nom }}</div>
                            <div class="text-sm text-gray-500 sous-classe-description">{{ Str::limit($sousClasse->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 sous-classe-parent">
                            Classe {{ $sousClasse->classe_parent }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 sous-classe-type">
                            {{ ucfirst($sousClasse->type_nature) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $sousClasse->comptes_count ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('classes-comptables.show', $sousClasse) }}" 
                               class="text-blue-600 hover:text-blue-900 transition-colors">Voir</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<script>
    // Fonctionnalité de recherche
    const searchInput = document.getElementById('searchInput');
    const classesPrincipales = document.getElementById('classesPrincipales');
    const sousClassesTable = document.getElementById('sousClassesTable');

    searchInput?.addEventListener('input', (e) => {
        const value = e.target.value.trim().toLowerCase();
        
        // Recherche dans les classes principales
        if (classesPrincipales) {
            classesPrincipales.querySelectorAll('.classe-card').forEach(card => {
                const nom = card.querySelector('.classe-nom')?.textContent.trim().toLowerCase() || '';
                const type = card.querySelector('.classe-type')?.textContent.trim().toLowerCase() || '';
                const description = card.querySelector('.classe-description')?.textContent.trim().toLowerCase() || '';
                const numero = card.querySelector('.w-12')?.textContent.trim().toLowerCase() || '';
                
                const searchText = `${nom} ${type} ${description} ${numero}`;
                card.style.display = searchText.includes(value) ? '' : 'none';
            });
        }
        
        // Recherche dans les sous-classes
        if (sousClassesTable) {
            sousClassesTable.querySelectorAll('.sous-classe-row').forEach(row => {
                const numero = row.querySelector('.sous-classe-numero')?.textContent.trim().toLowerCase() || '';
                const nom = row.querySelector('.sous-classe-nom')?.textContent.trim().toLowerCase() || '';
                const description = row.querySelector('.sous-classe-description')?.textContent.trim().toLowerCase() || '';
                const parent = row.querySelector('.sous-classe-parent')?.textContent.trim().toLowerCase() || '';
                const type = row.querySelector('.sous-classe-type')?.textContent.trim().toLowerCase() || '';
                
                const searchText = `${numero} ${nom} ${description} ${parent} ${type}`;
                row.style.display = searchText.includes(value) ? '' : 'none';
            });
        }
    });
</script>

@endsection
