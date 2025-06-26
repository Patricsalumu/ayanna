@extends('layouts.app')

@section('title', 'Transferts Inter-Comptes')

@section('content')
<div class="container-fluid">
    <!-- En-tête avec titre et boutons d'action -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-exchange-alt text-primary"></i>
                Transferts Inter-Comptes
            </h1>
            <p class="text-muted mb-0">Gérez les mouvements entre vos différents comptes et caisses</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transfertModal">
                <i class="fas fa-plus"></i> Nouveau transfert
            </button>
        </div>
    </div>

    <!-- Boutons de transferts rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-success">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt"></i> Transferts Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($pointsDeVente as $pdv)
                            @if($pdv->compte_caisse_id)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-left-info h-100">
                                        <div class="card-body">
                                            <h6 class="text-info font-weight-bold">{{ $pdv->nom }}</h6>
                                            <p class="text-sm text-muted mb-2">
                                                Solde: <span class="font-weight-bold">{{ number_format($pdv->compteCaisse->solde ?? 0, 0, ',', ' ') }} F</span>
                                            </p>
                                            <div class="btn-group w-100" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-transfert-rapide" 
                                                        data-source="{{ $pdv->compte_caisse_id }}" 
                                                        data-source-nom="{{ $pdv->nom }}"
                                                        data-type="banque">
                                                    <i class="fas fa-university"></i> → Banque
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success btn-transfert-rapide" 
                                                        data-source="{{ $pdv->compte_caisse_id }}" 
                                                        data-source-nom="{{ $pdv->nom }}"
                                                        data-type="caisse-generale">
                                                    <i class="fas fa-cash-register"></i> → Caisse
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique des transferts -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history"></i> Historique des Transferts
            </h6>
            <div class="d-flex gap-2">
                <input type="date" class="form-control form-control-sm" id="dateFilter" value="{{ date('Y-m-d') }}">
                <button class="btn btn-sm btn-outline-secondary" onclick="filtrerTransferts()">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="transfertsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Référence</th>
                            <th>Source</th>
                            <th>Destination</th>
                            <th>Montant</th>
                            <th>Libellé</th>
                            <th>Utilisateur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transferts as $transfert)
                            <tr>
                                <td>{{ $transfert->date_ecriture }}</td>
                                <td>
                                    <code>{{ $transfert->numero_piece }}</code>
                                </td>
                                <td>
                                    @php
                                        $compteSource = $transfert->ecritures->where('credit', '>', 0)->first();
                                    @endphp
                                    @if($compteSource)
                                        <span class="badge badge-danger">{{ $compteSource->compte->nom }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $compteDestination = $transfert->ecritures->where('debit', '>', 0)->first();
                                    @endphp
                                    @if($compteDestination)
                                        <span class="badge badge-success">{{ $compteDestination->compte->nom }}</span>
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold">
                                    {{ number_format($transfert->montant_total, 0, ',', ' ') }} F
                                </td>
                                <td>{{ $transfert->libelle }}</td>
                                <td>
                                    <small>{{ $transfert->user->name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick="voirDetails({{ $transfert->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    Aucun transfert trouvé pour aujourd'hui
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouveau Transfert -->
<div class="modal fade" id="transfertModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Nouveau Transfert
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transfertForm" method="POST" action="{{ route('transferts.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compte_source">Compte Source *</label>
                                <select class="form-control" id="compte_source" name="compte_source_id" required>
                                    <option value="">Sélectionner le compte source</option>
                                    @foreach($comptes as $compte)
                                        <option value="{{ $compte->id }}" data-solde="{{ $compte->solde ?? 0 }}">
                                            {{ $compte->nom }} ({{ number_format($compte->solde ?? 0, 0, ',', ' ') }} F)
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Compte qui donne l'argent</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compte_destination">Compte Destination *</label>
                                <select class="form-control" id="compte_destination" name="compte_destination_id" required>
                                    <option value="">Sélectionner le compte destination</option>
                                    @foreach($comptes as $compte)
                                        <option value="{{ $compte->id }}">
                                            {{ $compte->nom }} ({{ number_format($compte->solde ?? 0, 0, ',', ' ') }} F)
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Compte qui reçoit l'argent</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="montant">Montant *</label>
                                <input type="number" class="form-control" id="montant" name="montant" 
                                       min="1" step="1" required>
                                <small class="text-muted">Montant à transférer en FCFA</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reference">Référence (optionnel)</label>
                                <input type="text" class="form-control" id="reference" name="reference" 
                                       placeholder="Ex: Dépôt banque du 26/06">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="libelle">Libellé du transfert *</label>
                        <textarea class="form-control" id="libelle" name="libelle" rows="3" required 
                                  placeholder="Description du transfert..."></textarea>
                    </div>

                    <!-- Aperçu du transfert -->
                    <div class="alert alert-info d-none" id="apercu-transfert">
                        <h6><i class="fas fa-info-circle"></i> Aperçu du transfert</h6>
                        <div id="apercu-details"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Valider le transfert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transfert Rapide -->
<div class="modal fade" id="transfertRapideModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-bolt"></i> Transfert Rapide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transfertRapideForm" method="POST" action="{{ route('transferts.store') }}">
                @csrf
                <input type="hidden" id="rapid_source_id" name="compte_source_id">
                <input type="hidden" id="rapid_destination_id" name="compte_destination_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div id="rapid_summary"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="rapid_montant">Montant à transférer *</label>
                        <input type="number" class="form-control" id="rapid_montant" name="montant" 
                               min="1" step="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rapid_libelle">Libellé *</label>
                        <textarea class="form-control" id="rapid_libelle" name="libelle" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-bolt"></i> Transférer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des transferts rapides
    document.querySelectorAll('.btn-transfert-rapide').forEach(button => {
        button.addEventListener('click', function() {
            const sourceId = this.dataset.source;
            const sourceNom = this.dataset.sourceNom;
            const type = this.dataset.type;
            
            let destinationId, destinationNom, libelle;
            
            if (type === 'banque') {
                // Trouver le compte banque
                destinationId = '{{ $compteBanque->id ?? "" }}';
                destinationNom = '{{ $compteBanque->nom ?? "Banque" }}';
                libelle = `Dépôt banque depuis ${sourceNom}`;
            } else if (type === 'caisse-generale') {
                // Trouver la caisse générale
                destinationId = '{{ $caisseGenerale->id ?? "" }}';
                destinationNom = '{{ $caisseGenerale->nom ?? "Caisse Générale" }}';
                libelle = `Transfert vers caisse générale depuis ${sourceNom}`;
            }
            
            if (!destinationId) {
                alert('Compte de destination non configuré');
                return;
            }
            
            // Remplir le modal de transfert rapide
            document.getElementById('rapid_source_id').value = sourceId;
            document.getElementById('rapid_destination_id').value = destinationId;
            document.getElementById('rapid_libelle').value = libelle;
            document.getElementById('rapid_summary').innerHTML = `
                <strong>Source:</strong> ${sourceNom}<br>
                <strong>Destination:</strong> ${destinationNom}
            `;
            
            // Ouvrir le modal
            new bootstrap.Modal(document.getElementById('transfertRapideModal')).show();
        });
    });
    
    // Aperçu du transfert normal
    function mettreAJourApercu() {
        const sourceSelect = document.getElementById('compte_source');
        const destSelect = document.getElementById('compte_destination');
        const montantInput = document.getElementById('montant');
        const libelle = document.getElementById('libelle').value;
        
        if (sourceSelect.value && destSelect.value && montantInput.value && libelle) {
            const sourceNom = sourceSelect.options[sourceSelect.selectedIndex].text;
            const destNom = destSelect.options[destSelect.selectedIndex].text;
            const montant = parseInt(montantInput.value);
            
            document.getElementById('apercu-details').innerHTML = `
                <strong>Source:</strong> ${sourceNom}<br>
                <strong>Destination:</strong> ${destNom}<br>
                <strong>Montant:</strong> ${montant.toLocaleString()} F<br>
                <strong>Libellé:</strong> ${libelle}
            `;
            document.getElementById('apercu-transfert').classList.remove('d-none');
        } else {
            document.getElementById('apercu-transfert').classList.add('d-none');
        }
    }
    
    // Écouter les changements pour l'aperçu
    ['compte_source', 'compte_destination', 'montant', 'libelle'].forEach(id => {
        document.getElementById(id).addEventListener('change', mettreAJourApercu);
        document.getElementById(id).addEventListener('input', mettreAJourApercu);
    });
    
    // Validation des formulaires
    document.getElementById('transfertForm').addEventListener('submit', function(e) {
        const source = document.getElementById('compte_source').value;
        const destination = document.getElementById('compte_destination').value;
        
        if (source === destination) {
            e.preventDefault();
            alert('Le compte source et destination ne peuvent pas être identiques');
            return false;
        }
    });
});

function filtrerTransferts() {
    const date = document.getElementById('dateFilter').value;
    window.location.href = `{{ route('transferts.index') }}?date=${date}`;
}

function voirDetails(journalId) {
    // Rediriger vers les détails du journal
    window.open(`{{ route('comptabilite.journal') }}?journal_id=${journalId}`, '_blank');
}
</script>
@endsection
