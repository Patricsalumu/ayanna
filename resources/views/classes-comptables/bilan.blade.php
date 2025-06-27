@extends('layouts.appsalle')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- En-tête -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📊 Bilan Comptable</h1>
            <p class="text-gray-600 mt-2">État de la situation financière au {{ date('d/m/Y') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('comptabilite.classes-comptables.index') }}" 
               class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700 shadow">
                ← Retour au plan comptable
            </a>
            <a href="{{ route('comptabilite.plan-comptable.compte-resultat') }}" 
               class="inline-flex items-center px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 shadow">
                📈 Compte de résultat
            </a>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- ACTIF -->
        <div class="bg-white rounded-lg shadow">
            <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg">
                <h2 class="text-xl font-bold">ACTIF</h2>
                <p class="text-blue-100 text-sm">Emplois des ressources</p>
            </div>
            <div class="p-6">
                @php $totalActif = 0; @endphp
                @foreach($actif as $classe)
                    @php 
                        $soldeClasse = $classe->comptes->sum('solde_net');
                        $totalActif += $soldeClasse;
                    @endphp
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <div>
                            <div class="font-medium text-gray-900">{{ $classe->nom }}</div>
                            <div class="text-sm text-gray-500">Classe {{ $classe->numero }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold {{ $soldeClasse >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                                {{ number_format(abs($soldeClasse), 0, ',', ' ') }} F
                            </div>
                            <div class="text-xs text-gray-400">{{ $classe->comptes->count() }} comptes</div>
                        </div>
                    </div>
                @endforeach
                
                <div class="mt-6 pt-4 border-t-2 border-blue-200">
                    <div class="flex justify-between items-center">
                        <div class="font-bold text-lg text-gray-900">TOTAL ACTIF</div>
                        <div class="font-bold text-xl {{ $totalActif >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            {{ number_format(abs($totalActif), 0, ',', ' ') }} F
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PASSIF -->
        <div class="bg-white rounded-lg shadow">
            <div class="bg-purple-600 text-white px-6 py-4 rounded-t-lg">
                <h2 class="text-xl font-bold">PASSIF</h2>
                <p class="text-purple-100 text-sm">Origine des ressources</p>
            </div>
            <div class="p-6">
                @php $totalPassif = 0; @endphp
                @foreach($passif as $classe)
                    @php 
                        $soldeClasse = $classe->comptes->sum('solde_net');
                        $totalPassif += $soldeClasse;
                    @endphp
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <div>
                            <div class="font-medium text-gray-900">{{ $classe->nom }}</div>
                            <div class="text-sm text-gray-500">Classe {{ $classe->numero }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold {{ $soldeClasse >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                                {{ number_format(abs($soldeClasse), 0, ',', ' ') }} F
                            </div>
                            <div class="text-xs text-gray-400">{{ $classe->comptes->count() }} comptes</div>
                        </div>
                    </div>
                @endforeach
                
                <div class="mt-6 pt-4 border-t-2 border-purple-200">
                    <div class="flex justify-between items-center">
                        <div class="font-bold text-lg text-gray-900">TOTAL PASSIF</div>
                        <div class="font-bold text-xl {{ $totalPassif >= 0 ? 'text-purple-600' : 'text-red-600' }}">
                            {{ number_format(abs($totalPassif), 0, ',', ' ') }} F
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Équilibre du bilan -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Vérification de l'équilibre</h3>
        @php $difference = $totalActif - $totalPassif; @endphp
        <div class="flex justify-between items-center">
            <div>
                <span class="text-gray-600">Différence (Actif - Passif) :</span>
            </div>
            <div class="font-bold text-xl {{ abs($difference) < 1000 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($difference, 0, ',', ' ') }} F
            </div>
        </div>
        
        @if(abs($difference) < 1000)
            <div class="mt-3 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                ✅ Le bilan est équilibré ! La différence est acceptable (< 1 000 F).
            </div>
        @else
            <div class="mt-3 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                ⚠️ Attention : Le bilan n'est pas équilibré. Vérifiez vos écritures comptables.
            </div>
        @endif
    </div>
</div>
@endsection
