@extends('layouts.appsalle')

@section('title', 'Journal Comptable')

@section('content')
@include('comptabilite.partials.nav')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Journal Comptable</h1>
                    <p class="text-blue-100">Suivi chronologique des écritures comptables</p>
                </div>
                <div class="flex space-x-2">
                    <button onclick="ouvrirModaleTransfert()" 
                            class="bg-emerald-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Passer écriture
                    </button>
                    <a href="{{ route('comptabilite.journal.export-pdf', request()->query()) }}" 
                       class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-gray-50 px-6 py-4 border-b">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                    <input type="date" name="date_debut" value="{{ $dateDebut }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_fin" value="{{ $dateFin }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Point de vente</label>
                    <select name="point_de_vente_id" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous</option>
                        @foreach($pointsDeVente as $pdv)
                            <option value="{{ $pdv->id }}" {{ $pointDeVenteId == $pdv->id ? 'selected' : '' }}>
                                {{ $pdv->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type d'opération</label>
                    <select name="type_operation" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Toutes</option>
                        <option value="vente" {{ $typeOperation == 'vente' ? 'selected' : '' }}>Vente</option>
                        <option value="achat" {{ $typeOperation == 'achat' ? 'selected' : '' }}>Achat</option>
                        <option value="od" {{ $typeOperation == 'od' ? 'selected' : '' }}>OD</option>
                        <option value="caisse" {{ $typeOperation == 'caisse' ? 'selected' : '' }}>Caisse</option>
                        <option value="paiement" {{ $typeOperation == 'paiement' ? 'selected' : '' }}>Paiement</option>
                        <option value="mouvement" {{ $typeOperation == 'mouvement' ? 'selected' : '' }}>Mouvement</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Filtrer
                </button>
            </form>
        </div>

        <!-- Liste des écritures -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Libellé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Point de vente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($journaux as $journal)
                        @php
                            $totalDebit = $journal->ecritures->sum('debit');
                            $totalCredit = $journal->ecritures->sum('credit');
                            $estAnnule = $journal->statut === 'annule';
                            $estBrouillon = $journal->statut === 'brouillon';
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $estAnnule ? 'bg-gray-100 opacity-60' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ \Carbon\Carbon::parse($journal->date_ecriture)->format('d/m/Y') }}</div>
                                @if(!empty($journal->heure_ecriture))
                                    <div class="text-gray-500 text-xs">
                                        {{ \Carbon\Carbon::parse($journal->heure_ecriture)->format('H:i:s') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ $journal->libelle }}</div>
                                @if($journal->reference)
                                    <div class="text-gray-500 text-xs">Réf: {{ $journal->reference }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $journal->pointDeVente->nom ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $typeColors = [
                                        'vente' => 'bg-green-100 text-green-800',
                                        'achat' => 'bg-orange-100 text-orange-800',
                                        'od' => 'bg-indigo-100 text-indigo-800',
                                        'caisse' => 'bg-cyan-100 text-cyan-800',
                                        'paiement' => 'bg-blue-100 text-blue-800',
                                        'mouvement' => 'bg-purple-100 text-purple-800'
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $typeColors[$journal->type_operation] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($journal->type_operation) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                @currency($journal->montant_total)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-col gap-2">
                                    <button onclick="voirDetail({{ $journal->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors text-left">
                                        <i class="fas fa-eye mr-1"></i>Détail
                                    </button>
                                    @if($estBrouillon)
                                        <form method="POST" action="{{ route('comptabilite.journal.valider', $journal) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-900 transition-colors text-left">
                                                <i class="fas fa-check mr-1"></i>Valider
                                            </button>
                                        </form>
                                        <button type="button"
                                                onclick="ouvrirConfirmationAnnulation('{{ route('comptabilite.journal.annuler', $journal) }}', '{{ addslashes($journal->libelle) }}')"
                                                class="text-red-600 hover:text-red-900 transition-colors text-left">
                                            <i class="fas fa-times mr-1"></i>Annuler
                                        </button>
                                    @elseif($estAnnule)
                                        <span class="text-gray-500">Annulée</span>
                                    @else
                                        <span class="text-green-600">Validée</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Détail des écritures (masqué par défaut) -->
                        <tr id="detail-{{ $journal->id }}" class="bg-gray-50 hidden">
                            <td colspan="6" class="px-6 py-4">
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <h4 class="font-medium text-gray-900 mb-3">Détail des écritures</h4>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                                <tr class="bg-gray-100">
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Compte</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700">Débit</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700">Crédit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($journal->ecritures as $ecriture)
                                                    <tr class="border-b border-gray-200">
                                                        <td class="px-3 py-2">
                                                            <div class="font-medium">{{ $ecriture->compte->numero }} - {{ $ecriture->compte->nom }}</div>
                                                            @if($ecriture->libelle_ecriture)
                                                                <div class="text-gray-500 text-xs">{{ $ecriture->libelle_ecriture }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-right {{ $ecriture->debit > 0 ? 'font-medium text-red-600' : 'text-gray-400' }}">
                                                            @if($ecriture->debit > 0)
                                                                @currency($ecriture->debit)
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-right {{ $ecriture->credit > 0 ? 'font-medium text-green-600' : 'text-gray-400' }}">
                                                            @if($ecriture->credit > 0)
                                                                @currency($ecriture->credit)
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr class="bg-gray-100 font-medium">
                                                    <td class="px-3 py-2">Total</td>
                                                    <td class="px-3 py-2 text-right text-red-600">@currency($totalDebit)</td>
                                                    <td class="px-3 py-2 text-right text-green-600">@currency($totalCredit)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-book text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg">Aucune écriture comptable trouvée</p>
                                <p class="text-sm">Modifiez vos critères de recherche</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($journaux->hasPages())
            <div class="bg-white px-6 py-3 border-t">
                {{ $journaux->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modale de transfert inter-comptes -->
<div id="modaleTransfert" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-12 mx-auto p-4 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-1">
            <!-- En-tête de la modale -->
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-edit text-emerald-500 mr-2"></i>
                    Passer une écriture comptable
                </h3>
                <button onclick="fermerModaleTransfert()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Formulaire de transfert -->
            <form id="formTransfert" action="{{ route('transferts.store') }}" method="POST" class="mt-6" novalidate>
                @csrf

                <div id="formErrors" class="{{ $errors->any() ? '' : 'hidden' }} mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle mt-0.5 text-red-500"></i>
                        <div class="ml-2">
                            <p class="font-medium text-red-800">Veuillez corriger les erreurs suivantes :</p>
                            <ul id="formErrorsList" class="mt-2 list-disc pl-5 text-sm text-red-700">
                                @if($errors->any())
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                @php
                    $user = Auth::user();
                    $entrepriseId = $user->entreprise_id ?? $user->entreprise->id;
                    $comptes = \App\Models\Compte::where('entreprise_id', $entrepriseId)
                        ->orderBy('type')
                        ->orderBy('nom')
                        ->get();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Sélection du compte à débiter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-arrow-up text-red-500 mr-1"></i>
                            Compte à débiter
                        </label>
                        <input type="text" id="compteSourceSearch" list="compteSourceOptions" autocomplete="off"
                               placeholder="Rechercher un compte à débiter..."
                               class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <p id="error-compteSourceSearch" class="mt-1 text-sm text-red-600 hidden"></p>
                        <datalist id="compteSourceOptions">
                            @foreach($comptes as $compte)
                                <option value="{{ $compte->nom }} ({{ $compte->numero }})" data-id="{{ $compte->id }}"></option>
                            @endforeach
                        </datalist>
                        <select name="compte_source_id" id="compteSource" required class="hidden">
                            <option value="">Sélectionner le compte source...</option>
                            @foreach($comptes as $compte)
                                <option value="{{ $compte->id }}" data-solde="{{ $compte->solde ?? 0 }}">
                                    {{ $compte->nom }} ({{ $compte->numero }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Le compte sera débité (diminué)
                        </p>
                    </div>

                    <!-- Sélection du compte à créditer -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-arrow-down text-green-500 mr-1"></i>
                            Compte à créditer
                        </label>
                        <input type="text" id="compteDestinationSearch" list="compteDestinationOptions" autocomplete="off"
                               placeholder="Rechercher un compte à créditer..."
                               class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <p id="error-compteDestinationSearch" class="mt-1 text-sm text-red-600 hidden"></p>
                        <datalist id="compteDestinationOptions">
                            @foreach($comptes as $compte)
                                <option value="{{ $compte->nom }} ({{ $compte->numero }})" data-id="{{ $compte->id }}"></option>
                            @endforeach
                        </datalist>
                        <select name="compte_destination_id" id="compteDestination" required class="hidden">
                            <option value="">Sélectionner le compte destination...</option>
                            @foreach($comptes as $compte)
                                <option value="{{ $compte->id }}">
                                    {{ $compte->nom }} ({{ $compte->numero }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Le compte sera crédité (augmenté)
                        </p>
                    </div>
                </div>

                <!-- Libellé -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-edit text-blue-500 mr-1"></i>
                        Libellé / Motif de l’écriture
                    </label>
                    <input type="text" name="libelle" id="libelleTransfert" value="{{ old('libelle') }}" required 
                           placeholder="Ex: Dépôt banque recettes du jour"
                           class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    <p id="error-libelleTransfert" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar text-gray-600 mr-1"></i>
                            Date
                        </label>
                        <input type="date" name="date_ecriture" id="dateEcriture" required
                               value="{{ old('date_ecriture', now()->toDateString()) }}"
                               class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <p id="error-dateEcriture" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <!-- Heure -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock text-gray-600 mr-1"></i>
                            Heure
                        </label>
                        <input type="time" name="heure_ecriture" id="heureEcriture" required
                               value="{{ old('heure_ecriture', now()->format('H:i')) }}"
                               class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <p id="error-heureEcriture" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <!-- Montant -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-coins text-yellow-600 mr-1"></i>
                            Montant
                        </label>
                        <input type="number" name="montant" id="montantTransfert" min="1" step="1" required 
                               value="{{ old('montant') }}"
                               placeholder="Ex: 50000"
                               class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <p id="error-montantTransfert" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <!-- Type d'écriture -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag text-purple-500 mr-1"></i>
                            Type journal
                        </label>
                        <select name="type_operation" id="typeOperation" required
                                class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Sélectionner le type...</option>
                            <option value="vente">Vente</option>
                            <option value="achat">Achat</option>
                            <option value="od">OD</option>
                            <option value="caisse">Caisse</option>
                        </select>
                        <p id="error-typeOperation" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>
                </div>

                <div class="mb-4">
                    <!-- Référence (optionnel) -->
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hashtag text-gray-500 mr-1"></i>
                        Référence (optionnel)
                    </label>
                    <input type="text" name="reference" placeholder="Ex: VIRT001" 
                           class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" onclick="fermerModaleTransfert()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Annuler
                    </button>
                    <button type="button" onclick="ouvrirConfirmationEcriture()"
                            class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>
                        Valider l'écriture
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Boîte de confirmation d'annulation de l’écriture -->
<div id="modaleConfirmationAnnulation" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-[70]">
    <div class="relative top-24 mx-auto p-4 border w-11/12 max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-1">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    Confirmer l’annulation
                </h3>
                <button onclick="fermerConfirmationAnnulation()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mt-4 text-sm text-gray-700 space-y-2">
                <p>Vous êtes sur le point d’annuler l’écriture suivante :</p>
                <div class="bg-gray-50 rounded p-3">
                    <div id="annulationJournalLibelle" class="font-medium text-gray-900"></div>
                </div>
                <p class="text-red-600">Cette action ne créera pas d’écriture inverse. Elle grise simplement l’écriture concernée.</p>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t mt-4">
                <button type="button" onclick="fermerConfirmationAnnulation()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <form id="formAnnulationJournal" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Confirmer l’annulation
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Boîte de confirmation compacte -->
<div id="modaleConfirmationEcriture" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-[60]">
    <div class="relative top-24 mx-auto p-4 border w-11/12 max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-1">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                    Confirmation de l’écriture
                </h3>
                <button onclick="fermerConfirmationEcriture()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mt-4 text-sm text-gray-700 space-y-2">
                <div>• Débit : <span id="confirmDebit" class="font-medium"></span></div>
                <div>• Crédit : <span id="confirmCredit" class="font-medium"></span></div>
                <div>• Montant : <span id="confirmMontant" class="font-medium"></span></div>
                <div>• Type : <span id="confirmType" class="font-medium"></span></div>
                <div>• Date : <span id="confirmDate" class="font-medium"></span></div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t mt-4">
                <button type="button" onclick="fermerConfirmationEcriture()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button type="button" onclick="confirmerEnregistrement()"
                        class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function afficherErreurChamp(champId, message) {
    const input = document.getElementById(champId);
    const errorEl = document.getElementById(`error-${champId}`);

    if (input) {
        input.classList.add('border-red-500');
    }

    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }
}

function effacerErreursChamp() {
    document.querySelectorAll('#formTransfert input, #formTransfert select').forEach(function(el) {
        el.classList.remove('border-red-500');
    });

    document.querySelectorAll('[id^="error-"]')?.forEach(function(el) {
        el.textContent = '';
        el.classList.add('hidden');
    });
}

function validerFormulaireTransfert() {
    effacerErreursChamp();

    const erreurs = [];

    const compteSourceId = document.getElementById('compteSource').value;
    if (!compteSourceId) {
        afficherErreurChamp('compteSourceSearch', 'Veuillez sélectionner un compte à débiter.');
        erreurs.push('Compte à débiter requis');
    }

    const compteDestinationId = document.getElementById('compteDestination').value;
    if (!compteDestinationId) {
        afficherErreurChamp('compteDestinationSearch', 'Veuillez sélectionner un compte à créditer.');
        erreurs.push('Compte à créditer requis');
    }

    const libelle = document.getElementById('libelleTransfert').value.trim();
    if (!libelle) {
        afficherErreurChamp('libelleTransfert', 'Le libellé est obligatoire.');
        erreurs.push('Libellé requis');
    }

    const dateEcriture = document.getElementById('dateEcriture').value.trim();
    if (!dateEcriture) {
        afficherErreurChamp('dateEcriture', 'La date est obligatoire.');
        erreurs.push('Date requise');
    }

    const heureEcriture = document.getElementById('heureEcriture').value.trim();
    if (!heureEcriture) {
        afficherErreurChamp('heureEcriture', 'L\'heure est obligatoire.');
        erreurs.push('Heure requise');
    }

    const montant = document.getElementById('montantTransfert').value.trim();
    if (!montant || Number(montant) <= 0) {
        afficherErreurChamp('montantTransfert', 'Le montant doit être supérieur à 0.');
        erreurs.push('Montant invalide');
    }

    const typeOperation = document.getElementById('typeOperation').value;
    if (!typeOperation) {
        afficherErreurChamp('typeOperation', 'Le type de journal est obligatoire.');
        erreurs.push('Type de journal requis');
    }

    const formErrors = document.getElementById('formErrors');
    const formErrorsList = document.getElementById('formErrorsList');

    if (erreurs.length > 0) {
        if (formErrors) {
            formErrors.classList.remove('hidden');
        }
        if (formErrorsList) {
            formErrorsList.innerHTML = erreurs.map(function(message) {
                return '<li>' + message + '</li>';
            }).join('');
        }
        return false;
    }

    if (formErrors) {
        formErrors.classList.add('hidden');
    }

    return true;
}

function voirDetail(journalId) {
    const detailRow = document.getElementById(`detail-${journalId}`);
    if (detailRow.classList.contains('hidden')) {
        detailRow.classList.remove('hidden');
    } else {
        detailRow.classList.add('hidden');
    }
}

// Fonctions pour la modale de transfert
function ouvrirModaleTransfert() {
    document.getElementById('modaleTransfert').classList.remove('hidden');
    document.getElementById('formTransfert').reset();
    effacerErreursChamp();
    document.getElementById('formErrors').classList.add('hidden');
    document.getElementById('resumeTransfert').classList.add('hidden');
}

function fermerModaleTransfert() {
    document.getElementById('modaleTransfert').classList.add('hidden');
}

function ouvrirConfirmationEcriture() {
    if (!validerFormulaireTransfert()) {
        return;
    }

    const compteSourceId = document.getElementById('compteSource').value;
    const compteDestinationId = document.getElementById('compteDestination').value;
    const montant = document.getElementById('montantTransfert').value;
    const typeOperation = document.getElementById('typeOperation').value;
    const dateEcriture = document.getElementById('dateEcriture').value;

    const sourceOption = document.querySelector(`#compteSource option[value="${compteSourceId}"]`);
    const destinationOption = document.querySelector(`#compteDestination option[value="${compteDestinationId}"]`);

    if (!sourceOption || !destinationOption) {
        return;
    }

    document.getElementById('confirmDebit').textContent = sourceOption.textContent.split('(')[0].trim();
    document.getElementById('confirmCredit').textContent = destinationOption.textContent.split('(')[0].trim();
    document.getElementById('confirmMontant').textContent = new Intl.NumberFormat('fr-FR').format(montant) + ' ' + currencySymbol;
    document.getElementById('confirmType').textContent = document.querySelector(`#typeOperation option[value="${typeOperation}"]`)?.textContent || typeOperation;
    document.getElementById('confirmDate').textContent = dateEcriture;

    document.getElementById('modaleConfirmationEcriture').classList.remove('hidden');
}

function fermerConfirmationEcriture() {
    document.getElementById('modaleConfirmationEcriture').classList.add('hidden');
}

function ouvrirConfirmationAnnulation(url, libelle) {
    const form = document.getElementById('formAnnulationJournal');
    const modal = document.getElementById('modaleConfirmationAnnulation');
    const libelleElement = document.getElementById('annulationJournalLibelle');

    form.action = url;
    libelleElement.textContent = libelle || 'Écriture comptable';
    modal.classList.remove('hidden');
}

function fermerConfirmationAnnulation() {
    document.getElementById('modaleConfirmationAnnulation').classList.add('hidden');
}

function confirmerEnregistrement() {
    if (!validerFormulaireTransfert()) {
        fermerConfirmationEcriture();
        return;
    }

    fermerConfirmationEcriture();
    document.getElementById('formTransfert').submit();
}

function transfertRapide(type, compteDestinationId) {
    // Définir le libellé selon le type
    const compteDestination = document.querySelector(`option[value="${compteDestinationId}"]`);
    const nomDestination = compteDestination ? compteDestination.textContent.split('(')[0].trim() : '';
    
    let libelle = '';
    if (type === 'banque') {
        libelle = `Dépôt banque recettes du jour - ${new Date().toLocaleDateString('fr-FR')}`;
    } else if (type === 'caisse') {
        libelle = `Transfert vers caisse générale - ${new Date().toLocaleDateString('fr-FR')}`;
    }
    
    // Remplir les champs
    document.getElementById('compteDestination').value = compteDestinationId;
    document.getElementById('libelleTransfert').value = libelle;
    
    // Mettre à jour le résumé
    mettreAJourResume();
}

function mettreAJourResume() {
    const compteSourceId = document.getElementById('compteSource').value;
    const compteDestinationId = document.getElementById('compteDestination').value;
    const montant = document.getElementById('montantTransfert').value;
    
    if (compteSourceId && compteDestinationId && montant) {
        const sourceOption = document.querySelector(`#compteSource option[value="${compteSourceId}"]`);
        const destinationOption = document.querySelector(`#compteDestination option[value="${compteDestinationId}"]`);
        
        if (sourceOption && destinationOption) {
            document.getElementById('confirmDebit').textContent = sourceOption.textContent.split('(')[0].trim();
            document.getElementById('confirmCredit').textContent = destinationOption.textContent.split('(')[0].trim();
            document.getElementById('confirmMontant').textContent = new Intl.NumberFormat('fr-FR').format(montant) + ' ' + currencySymbol;
        }
    }
}

function synchroniserRechercheCompte(inputId, listId, selectId) {
    const input = document.getElementById(inputId);
    const select = document.getElementById(selectId);
    const datalist = document.getElementById(listId);

    if (!input || !select || !datalist) {
        return;
    }

    input.addEventListener('input', function() {
        const valeurSaisie = this.value.trim();
        const optionTrouvee = Array.from(datalist.options).find(option => option.value.trim() === valeurSaisie);

        if (optionTrouvee && optionTrouvee.dataset.id) {
            select.value = optionTrouvee.dataset.id;
            mettreAJourResume();
        }
    });
}

const currencySymbol = @json(optional(Auth::user()->entreprise)->devise ?? '$');
// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    synchroniserRechercheCompte('compteSourceSearch', 'compteSourceOptions', 'compteSource');
    synchroniserRechercheCompte('compteDestinationSearch', 'compteDestinationOptions', 'compteDestination');

    document.getElementById('compteSource').addEventListener('change', mettreAJourResume);
    document.getElementById('compteDestination').addEventListener('change', mettreAJourResume);
    document.getElementById('montantTransfert').addEventListener('input', mettreAJourResume);
    
    // Fermer la modale en cliquant à l'extérieur
    document.getElementById('modaleTransfert').addEventListener('click', function(e) {
        if (e.target === this) {
            fermerModaleTransfert();
        }
    });
    
    // Empêcher la sélection du même compte source et destination
    document.getElementById('compteSource').addEventListener('change', function() {
        const sourceId = this.value;
        const destinationSelect = document.getElementById('compteDestination');
        
        Array.from(destinationSelect.options).forEach(option => {
            if (option.value === sourceId) {
                option.disabled = true;
                option.classList.add('text-gray-400');
            } else {
                option.disabled = false;
                option.classList.remove('text-gray-400');
            }
        });
        
        // Si le compte de destination était le même que la source, le reset
        if (destinationSelect.value === sourceId) {
            destinationSelect.value = '';
            mettreAJourResume();
        }
    });
    
    document.getElementById('compteDestination').addEventListener('change', function() {
        const destinationId = this.value;
        const sourceSelect = document.getElementById('compteSource');
        
        Array.from(sourceSelect.options).forEach(option => {
            if (option.value === destinationId) {
                option.disabled = true;
                option.classList.add('text-gray-400');
            } else {
                option.disabled = false;
                option.classList.remove('text-gray-400');
            }
        });
        
        // Si le compte source était le même que la destination, le reset
        if (sourceSelect.value === destinationId) {
            sourceSelect.value = '';
            mettreAJourResume();
        }
    });
});
</script>
@endsection
