@extends('layouts.app')

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
                        <i class="fas fa-exchange-alt mr-2"></i>Nouveau transfert
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
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($journal->date_ecriture)->format('d/m/Y H:i') }}
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
                                        'paiement' => 'bg-blue-100 text-blue-800',
                                        'mouvement' => 'bg-purple-100 text-purple-800'
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $typeColors[$journal->type_operation] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($journal->type_operation) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ number_format($journal->montant_total, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="voirDetail({{ $journal->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors">
                                    <i class="fas fa-eye mr-1"></i>Détail
                                </button>
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
                                                            {{ $ecriture->debit > 0 ? number_format($ecriture->debit, 0, ',', ' ') : '-' }}
                                                        </td>
                                                        <td class="px-3 py-2 text-right {{ $ecriture->credit > 0 ? 'font-medium text-green-600' : 'text-gray-400' }}">
                                                            {{ $ecriture->credit > 0 ? number_format($ecriture->credit, 0, ',', ' ') : '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr class="bg-gray-100 font-medium">
                                                    <td class="px-3 py-2">Total</td>
                                                    <td class="px-3 py-2 text-right text-red-600">{{ number_format($totalDebit, 0, ',', ' ') }}</td>
                                                    <td class="px-3 py-2 text-right text-green-600">{{ number_format($totalCredit, 0, ',', ' ') }}</td>
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
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- En-tête de la modale -->
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-exchange-alt text-emerald-500 mr-2"></i>
                    Nouveau transfert inter-comptes
                </h3>
                <button onclick="fermerModaleTransfert()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Formulaire de transfert -->
            <form id="formTransfert" action="{{ route('transferts.store') }}" method="POST" class="mt-6">
                @csrf
                
                <!-- Sélection du compte source -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-arrow-up text-red-500 mr-1"></i>
                        Compte source (d'où vient l'argent)
                    </label>
                    <select name="compte_source_id" id="compteSource" required 
                            class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Sélectionner le compte source...</option>
                        @php
                            $user = Auth::user();
                            $entrepriseId = $user->entreprise_id ?? $user->entreprise->id;
                            $comptes = \App\Models\Compte::where('entreprise_id', $entrepriseId)
                                ->orderBy('type')
                                ->orderBy('nom')
                                ->get();
                        @endphp
                        @foreach($comptes as $compte)
                            <option value="{{ $compte->id }}" data-solde="{{ $compte->solde ?? 0 }}">
                                {{ $compte->nom }} ({{ $compte->numero }})
                                @if($compte->type === 'actif')
                                    - Solde: {{ number_format($compte->solde ?? 0, 0, ',', ' ') }} F
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Le compte sera débité (diminué)
                    </p>
                </div>

                <!-- Sélection du compte destination -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-arrow-down text-green-500 mr-1"></i>
                        Compte destination (où va l'argent)
                    </label>
                    <select name="compte_destination_id" id="compteDestination" required 
                            class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
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

                <!-- Boutons de transfert rapide -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-bolt text-yellow-500 mr-1"></i>
                        Transferts rapides
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @php
                            $compteBanque = $comptes->where('nom', 'LIKE', '%banque%')->first() 
                                ?? $comptes->where('numero', '512')->first();
                            $caisseGenerale = $comptes->where('nom', 'LIKE', '%caisse générale%')->first()
                                ?? $comptes->where('nom', 'LIKE', '%caisse%')->where('numero', '531')->first();
                        @endphp
                        
                        @if($compteBanque)
                        <button type="button" onclick="transfertRapide('banque', {{ $compteBanque->id }})" 
                                class="flex items-center justify-center px-3 py-2 border border-blue-300 rounded-lg text-blue-700 hover:bg-blue-50 transition-colors">
                            <i class="fas fa-university mr-2"></i>
                            Vers banque
                        </button>
                        @endif
                        
                        @if($caisseGenerale)
                        <button type="button" onclick="transfertRapide('caisse', {{ $caisseGenerale->id }})" 
                                class="flex items-center justify-center px-3 py-2 border border-green-300 rounded-lg text-green-700 hover:bg-green-50 transition-colors">
                            <i class="fas fa-cash-register mr-2"></i>
                            Vers caisse générale
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Montant -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-coins text-yellow-600 mr-1"></i>
                        Montant à transférer (FCFA)
                    </label>
                    <input type="number" name="montant" id="montantTransfert" min="1" step="1" required 
                           placeholder="Ex: 50000"
                           class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    <div id="alerteSolde" class="hidden mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-yellow-800 text-sm">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <span id="messageSolde"></span>
                    </div>
                </div>

                <!-- Libellé -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-edit text-blue-500 mr-1"></i>
                        Libellé / Motif du transfert
                    </label>
                    <input type="text" name="libelle" id="libelleTransfert" required 
                           placeholder="Ex: Dépôt banque recettes du jour"
                           class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <!-- Référence (optionnel) -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hashtag text-gray-500 mr-1"></i>
                        Référence (optionnel)
                    </label>
                    <input type="text" name="reference" placeholder="Ex: VIRT001" 
                           class="w-full border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <!-- Résumé du transfert -->
                <div id="resumeTransfert" class="hidden mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <h4 class="font-medium text-emerald-800 mb-2">Résumé du transfert :</h4>
                    <div class="text-sm text-emerald-700">
                        <div>• Débit : <span id="resumeSource"></span></div>
                        <div>• Crédit : <span id="resumeDestination"></span></div>
                        <div>• Montant : <span id="resumeMontant"></span></div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" onclick="fermerModaleTransfert()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>
                        Effectuer le transfert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
    // Reset du formulaire
    document.getElementById('formTransfert').reset();
    document.getElementById('resumeTransfert').classList.add('hidden');
    document.getElementById('alerteSolde').classList.add('hidden');
}

function fermerModaleTransfert() {
    document.getElementById('modaleTransfert').classList.add('hidden');
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
            document.getElementById('resumeSource').textContent = sourceOption.textContent.split('(')[0].trim();
            document.getElementById('resumeDestination').textContent = destinationOption.textContent.split('(')[0].trim();
            document.getElementById('resumeMontant').textContent = new Intl.NumberFormat('fr-FR').format(montant) + ' FCFA';
            document.getElementById('resumeTransfert').classList.remove('hidden');
        }
    } else {
        document.getElementById('resumeTransfert').classList.add('hidden');
    }
}

function verifierSolde() {
    const compteSourceId = document.getElementById('compteSource').value;
    const montant = parseFloat(document.getElementById('montantTransfert').value) || 0;
    
    if (compteSourceId && montant > 0) {
        const sourceOption = document.querySelector(`#compteSource option[value="${compteSourceId}"]`);
        const solde = parseFloat(sourceOption.getAttribute('data-solde')) || 0;
        
        if (solde < montant) {
            document.getElementById('messageSolde').textContent = 
                `Attention: le solde du compte (${new Intl.NumberFormat('fr-FR').format(solde)} F) est insuffisant pour ce transfert (${new Intl.NumberFormat('fr-FR').format(montant)} F)}`;
            document.getElementById('alerteSolde').classList.remove('hidden');
        } else {
            document.getElementById('alerteSolde').classList.add('hidden');
        }
    } else {
        document.getElementById('alerteSolde').classList.add('hidden');
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Écouter les changements dans les selects et inputs
    document.getElementById('compteSource').addEventListener('change', function() {
        mettreAJourResume();
        verifierSolde();
    });
    
    document.getElementById('compteDestination').addEventListener('change', mettreAJourResume);
    
    document.getElementById('montantTransfert').addEventListener('input', function() {
        mettreAJourResume();
        verifierSolde();
    });
    
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
