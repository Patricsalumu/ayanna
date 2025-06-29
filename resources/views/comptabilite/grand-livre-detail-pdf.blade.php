<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Livre - {{ $compte->numero }} - {{ $compte->nom }}</title>
    <style>
        @page {
            margin: 1cm;
            size: A4;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #10B981;
        }
        
        .header h1 {
            color: #10B981;
            font-size: 24px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        .compte-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        
        .compte-info h2 {
            color: #10B981;
            font-size: 18px;
            margin: 0 0 10px 0;
        }
        
        .compte-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .compte-details .detail {
            flex: 1;
            margin-right: 15px;
        }
        
        .compte-details .detail:last-child {
            margin-right: 0;
        }
        
        .detail-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        
        .detail-value {
            font-weight: bold;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #dee2e6;
            text-transform: uppercase;
            font-size: 9px;
        }
        
        td {
            padding: 6px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .solde-initial {
            background-color: #e7f5ff !important;
            font-weight: bold;
        }
        
        .montant {
            text-align: right;
            font-weight: bold;
        }
        
        .montant.debit {
            color: #dc3545;
        }
        
        .montant.credit {
            color: #28a745;
        }
        
        .montant.vide {
            color: #999;
        }
        
        .solde {
            font-weight: bold;
        }
        
        .solde.positif {
            color: #28a745;
        }
        
        .solde.negatif {
            color: #dc3545;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <!-- En-tête avec logo et informations entreprise -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #10B981;">
        <!-- Logo et informations entreprise à gauche -->
        <div style="flex: 1;">
            @if(Auth::user()->entreprise && Auth::user()->entreprise->logo)
                <img src="{{ public_path('storage/' . Auth::user()->entreprise->logo) }}" 
                     alt="Logo" 
                     style="max-height: 60px; max-width: 120px; margin-bottom: 10px;">
            @endif
            <div style="font-size: 12px; color: #333; line-height: 1.4;">
                @if(Auth::user()->entreprise)
                    <div style="font-weight: bold; font-size: 14px; color: #10B981;">{{ Auth::user()->entreprise->nom }}</div>
                    @if(Auth::user()->entreprise->numero_entreprise)
                        <div>N° Entreprise : {{ Auth::user()->entreprise->numero_entreprise }}</div>
                    @endif
                    @if(Auth::user()->entreprise->adresse)
                        <div>{{ Auth::user()->entreprise->adresse }}</div>
                    @endif
                    @if(Auth::user()->entreprise->telephone)
                        <div>Tél : {{ Auth::user()->entreprise->telephone }}</div>
                    @endif
                    @if(Auth::user()->entreprise->email)
                        <div>Email : {{ Auth::user()->entreprise->email }}</div>
                    @endif
                @endif
            </div>
        </div>
        
        <!-- Titre et date au centre-droit -->
        <div style="flex: 1; text-align: center;">
            <h1 style="color: #10B981; font-size: 24px; margin: 0 0 5px 0; font-weight: bold;">GRAND LIVRE</h1>
            <p style="color: #666; font-size: 14px; margin: 0 0 10px 0;">{{ $compte->numero }} - {{ $compte->nom }}</p>
            <p style="color: #666; font-size: 12px; margin: 0 0 10px 0;">Du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
            
            <!-- Mention générée par Ayanna -->
            <div style="background: #f8f9fa; padding: 8px 12px; border-radius: 5px; border: 1px solid #e9ecef; margin-top: 15px;">
                <div style="font-size: 10px; color: #666; text-align: center;">
                    <strong>📄 Généré par Ayanna</strong><br>
                    Le {{ now()->format('d/m/Y à H:i') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Informations du compte -->
    <div class="compte-info">
        <div class="compte-details">
            <div class="detail">
                <div class="detail-label">Type de compte</div>
                <div class="detail-value">{{ ucfirst($compte->type) }}</div>
            </div>
            <div class="detail">
                <div class="detail-label">Classe comptable</div>
                <div class="detail-value">{{ $compte->classeComptable->nom ?? 'N/A' }}</div>
            </div>
            <div class="detail">
                <div class="detail-label">Solde initial</div>
                <div class="detail-value {{ $soldeInitial >= 0 ? 'solde positif' : 'solde negatif' }}">
                    {{ number_format($soldeInitial, 0, ',', ' ') }} FC
                </div>
            </div>
            @php
                $debitTotal = $ecritures->sum('debit');
                $creditTotal = $ecritures->sum('credit');
                if ($compte->type === 'actif') {
                    $soldeFinal = $soldeInitial + $debitTotal - $creditTotal;
                } else {
                    $soldeFinal = $soldeInitial + $creditTotal - $debitTotal;
                }
            @endphp
            <div class="detail">
                <div class="detail-label">Solde final</div>
                <div class="detail-value {{ $soldeFinal >= 0 ? 'solde positif' : 'solde negatif' }}">
                    {{ number_format($soldeFinal, 0, ',', ' ') }} FC
                </div>
            </div>
        </div>
    </div>

    <!-- Table des mouvements -->
    @if($ecritures->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 15%;">N° Pièce</th>
                    <th style="width: 35%;">Libellé</th>
                    <th style="width: 12%;">Débit</th>
                    <th style="width: 12%;">Crédit</th>
                    <th style="width: 14%;">Solde</th>
                </tr>
            </thead>
            <tbody>
                <!-- Ligne du solde initial -->
                <tr class="solde-initial">
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                    <td><strong>Solde initial</strong></td>
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                    <td class="montant {{ $soldeInitial >= 0 ? 'credit' : 'debit' }}">
                        {{ number_format($soldeInitial, 0, ',', ' ') }}
                    </td>
                </tr>
                
                @php $soldeProgressif = $soldeInitial; @endphp
                @foreach($ecritures as $ecriture)
                    @php
                        if ($compte->type === 'actif') {
                            $soldeProgressif += $ecriture->debit - $ecriture->credit;
                        } else {
                            $soldeProgressif += $ecriture->credit - $ecriture->debit;
                        }
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($ecriture->journal->date_ecriture)->format('d/m/Y') }}</td>
                        <td>{{ $ecriture->journal->numero_piece }}</td>
                        <td>{{ $ecriture->libelle }}</td>
                        <td class="montant {{ $ecriture->debit > 0 ? 'debit' : 'vide' }}">
                            {{ $ecriture->debit > 0 ? number_format($ecriture->debit, 0, ',', ' ') : '-' }}
                        </td>
                        <td class="montant {{ $ecriture->credit > 0 ? 'credit' : 'vide' }}">
                            {{ $ecriture->credit > 0 ? number_format($ecriture->credit, 0, ',', ' ') : '-' }}
                        </td>
                        <td class="montant {{ $soldeProgressif >= 0 ? 'credit' : 'debit' }}">
                            {{ number_format($soldeProgressif, 0, ',', ' ') }}
                        </td>
                    </tr>
                @endforeach
                
                <!-- Ligne des totaux -->
                <tr style="background-color: #e9ecef; font-weight: bold; border-top: 2px solid #495057;">
                    <td colspan="3" style="text-align: right; font-weight: bold;">TOTAUX :</td>
                    <td class="montant debit">{{ number_format($debitTotal, 0, ',', ' ') }}</td>
                    <td class="montant credit">{{ number_format($creditTotal, 0, ',', ' ') }}</td>
                    <td class="montant {{ $soldeFinal >= 0 ? 'credit' : 'debit' }}">
                        {{ number_format($soldeFinal, 0, ',', ' ') }}
                    </td>
                </tr>
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 5px; color: #666;">
            <p style="font-size: 14px; margin: 0;">Aucun mouvement trouvé pour cette période</p>
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>Grand Livre - {{ $compte->numero }} {{ $compte->nom }} - {{ $ecritures->count() }} mouvement(s) - Période du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
        <p>Document généré le {{ now()->format('d/m/Y à H:i:s') }}</p>
    </div>
</body>
</html>
