@extends('layouts.appsalle')

@section('title', 'Configuration Comptable POS')

@section('content')
@include('comptabilite.partials.nav')

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Configuration Comptable</h1>
                    <p class="text-purple-100">Association des points de vente aux comptes comptables</p>
                </div>
                <a href="{{ route('comptabilite.journal') }}" 
                   class="bg-white text-purple-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Retour au journal
                </a>
            </div>
        </div>

        <!-- Sélection du point de vente -->
        <div class="bg-gray-50 px-6 py-4 border-b">
            <div class="flex items-center gap-4">
                <label class="text-sm font-medium text-gray-700">Point de vente :</label>
                <select onchange="window.location.href = '{{ route('comptabilite.configuration-pdv') }}/' + this.value" 
                        class="border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Sélectionner un point de vente</option>
                    @foreach($pointsDeVente as $pdv)
                        <option value="{{ $pdv->id }}" {{ $pointDeVente && $pointDeVente->id == $pdv->id ? 'selected' : '' }}>
                            {{ $pdv->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($pointDeVente)
            <!-- Configuration du point de vente sélectionné -->
            <div class="p-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-store text-blue-600 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-900">{{ $pointDeVente->nom }}</h3>
                            <p class="text-blue-700 text-sm">Configuration comptable pour ce point de vente</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('comptabilite.sauvegarder-configuration-pdv', $pointDeVente->id) }}" class="space-y-6">
                    @csrf
                    
                    <!-- Activation de la comptabilité -->
                    <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="comptabilite_active" value="1" 
                                   {{ $pointDeVente->comptabilite_active ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-3 text-sm font-medium text-gray-900">
                                Activer la comptabilité automatique pour ce point de vente
                            </span>
                        </label>
                        <p class="mt-2 text-xs text-gray-600">
                            Si activé, les ventes, règlements et mouvements de stock seront préparés en brouillon à la fermeture de chaque session
                        </p>

                        <label class="flex items-center">
                            <input type="checkbox" name="interdire_commande_si_stock_null" value="1"
                                   {{ $pointDeVente->interdire_commande_si_stock_null ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-3 text-sm font-medium text-gray-900">
                                Interdire la commande des produits dont le stock est nul dans ce point de vente
                            </span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="serveuse_peut_valider_paiement" value="1"
                                   {{ $pointDeVente->serveuse_peut_valider_paiement ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-3 text-sm font-medium text-gray-900">
                                Autoriser les serveuses à valider elles-mêmes le paiement
                            </span>
                        </label>
                    </div>

                    <!-- Configuration des comptes -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Compte Caisse -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-cash-register text-green-600 mr-2"></i>
                                Compte Caisse
                            </label>
                            <select name="compte_caisse_id" class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Sélectionner un compte</option>
                                @foreach($comptesCaisse as $compte)
                                    <option value="{{ $compte->id }}" 
                                            {{ $pointDeVente->compte_caisse_id == $compte->id ? 'selected' : '' }}>
                                        {{ $compte->numero }} - {{ $compte->nom }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500">
                                Compte débité lors des encaissements en espèces
                            </p>
                        </div>

                        <!-- Compte Client -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-users text-orange-600 mr-2"></i>
                                Compte Client
                            </label>
                            <select name="compte_client_id" class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Sélectionner un compte</option>
                                @foreach($comptesClient as $compte)
                                    <option value="{{ $compte->id }}" 
                                            {{ $pointDeVente->compte_client_id == $compte->id ? 'selected' : '' }}>
                                        {{ $compte->numero }} - {{ $compte->nom }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500">
                                Compte de classe 4 débité pour les créances clients
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Compte de remise accordée (classe 6)
                            </label>
                            <select name="compte_remise_id" class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Sélectionner un compte de charge</option>
                                @foreach($comptesRemise as $compte)
                                    <option value="{{ $compte->id }}" @selected($pointDeVente->compte_remise_id == $compte->id)>
                                        {{ $compte->numero }} - {{ $compte->nom }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500">Débité du montant des remises; le compte client est crédité de la remise.</p>
                            @if($comptesRemise->isEmpty())
                                <p class="text-xs text-amber-700">Aucun compte de classe 6. <a class="underline" href="{{ route('comptes.create', ['entreprise_id' => auth()->user()->entreprise_id]) }}" target="_blank">Créer un compte</a>.</p>
                            @endif
                        </div>
                    </div>

                    <!-- État actuel -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">État actuel de la configuration</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                            <div class="flex items-center justify-between p-2 bg-white rounded border">
                                <span class="text-gray-600">Comptabilité :</span>
                                <span class="font-medium {{ $pointDeVente->comptabilite_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $pointDeVente->comptabilite_active ? 'Activée' : 'Désactivée' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-white rounded border">
                                <span class="text-gray-600">Compte Caisse :</span>
                                <span class="font-medium {{ $pointDeVente->compteCaisse ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $pointDeVente->compteCaisse ? $pointDeVente->compteCaisse->numero : 'Non configuré' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-white rounded border">
                                <span class="text-gray-600">Compte Client :</span>
                                <span class="font-medium {{ $pointDeVente->compteClient ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $pointDeVente->compteClient ? $pointDeVente->compteClient->numero : 'Non configuré' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-white rounded border">
                                <span class="text-gray-600">Compte Remise :</span>
                                <span class="font-medium {{ $pointDeVente->compteRemise ? 'text-green-600' : 'text-amber-600' }}">
                                    {{ $pointDeVente->compteRemise ? $pointDeVente->compteRemise->numero : 'Optionnel' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="window.history.back()" 
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Sauvegarder
                        </button>
                    </div>
                </form>
            </div>
        @else
            <!-- Aucun point de vente sélectionné -->
            <div class="p-12 text-center">
                <i class="fas fa-cog text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Configuration Comptable</h3>
                <p class="text-gray-600 mb-6">
                    Sélectionnez un point de vente ci-dessus pour configurer ses comptes comptables
                </p>
                
                <!-- Aide -->
                <div class="max-w-2xl mx-auto bg-blue-50 border border-blue-200 rounded-lg p-6 text-left">
                    <h4 class="font-medium text-blue-900 mb-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        Comment configurer la comptabilité ?
                    </h4>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-2 mt-0.5"></i>
                            <span>Choisissez un point de vente dans la liste déroulante</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-2 mt-0.5"></i>
                            <span>Associez-lui les comptes comptables appropriés (caisse, vente, clients)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-2 mt-0.5"></i>
                            <span>Activez la comptabilité automatique</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-2 mt-0.5"></i>
                            <span>Les ventes et paiements seront automatiquement enregistrés</span>
                        </li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
