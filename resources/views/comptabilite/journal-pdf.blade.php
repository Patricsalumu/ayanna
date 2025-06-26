<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Comptable - {{ $entreprise->nom }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
            color: #666;
        }
        
        .period {
            font-size: 12px;
            color: #888;
        }
        
        .journal-entry {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .entry-header {
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        
        .entry-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .entry-date {
            color: #0066cc;
        }
        
        .entry-amount {
            color: #28a745;
            font-weight: bold;
        }
        
        .ecritures-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .ecritures-table th {
            background-color: #f1f3f4;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }
        
        .ecritures-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
        }
        
        .ecritures-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .debit {
            text-align: right;
            color: #dc3545;
        }
        
        .credit {
            text-align: right;
            color: #28a745;
        }
        
        .montant {
            font-weight: bold;
        }
        
        .total-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .total-label {
            font-weight: bold;
        }
        
        .total-value {
            font-weight: bold;
            color: #333;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #888;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- En-tête du rapport -->
    <div class="header">
        <div class="company-name">{{ $entreprise->nom }}</div>
        <div class="report-title">JOURNAL COMPTABLE</div>
        <div class="period">
            Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} 
            au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        </div>
        <div class="period">
            Généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    @if($journaux->count() > 0)
        @php
            $totalDebit = 0;
            $totalCredit = 0;
            $entryCount = 0;
        @endphp

        @foreach($journaux as $index => $journal)
            @php
                $entryCount++;
                $journalDebit = $journal->ecritures->sum('debit');
                $journalCredit = $journal->ecritures->sum('credit');
                $totalDebit += $journalDebit;
                $totalCredit += $journalCredit;
            @endphp

            {{-- Saut de page tous les 3 journaux pour éviter la surcharge --}}
            @if($index > 0 && $index % 3 == 0)
                <div class="page-break"></div>
            @endif

            <div class="journal-entry">
                <!-- En-tête de l'écriture -->
                <div class="entry-header">
                    <div class="entry-info">
                        <div>
                            <strong>{{ $journal->numero_piece }}</strong> - {{ $journal->libelle }}
                            @if($journal->pointDeVente)
                                <span style="color: #666;">({{ $journal->pointDeVente->nom }})</span>
                            @endif
                        </div>
                        <div>
                            <span class="entry-date">{{ \Carbon\Carbon::parse($journal->date_ecriture)->format('d/m/Y') }}</span>
                            <span class="entry-amount">{{ number_format($journal->montant_total, 0, ',', ' ') }} F</span>
                        </div>
                    </div>
                </div>

                <!-- Détail des écritures -->
                @if($journal->ecritures->count() > 0)
                    <table class="ecritures-table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">N° Compte</th>
                                <th style="width: 35%;">Libellé</th>
                                <th style="width: 20%;">Débit</th>
                                <th style="width: 20%;">Crédit</th>
                                <th style="width: 10%;">Ordre</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journal->ecritures->sortBy('ordre') as $ecriture)
                                <tr>
                                    <td>{{ $ecriture->compte->numero ?? 'N/A' }}</td>
                                    <td>
                                        <strong>{{ $ecriture->compte->nom ?? 'Compte supprimé' }}</strong><br>
                                        <small style="color: #666;">{{ $ecriture->libelle }}</small>
                                    </td>
                                    <td class="debit">
                                        @if($ecriture->debit > 0)
                                            <span class="montant">{{ number_format($ecriture->debit, 0, ',', ' ') }} F</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="credit">
                                        @if($ecriture->credit > 0)
                                            <span class="montant">{{ number_format($ecriture->credit, 0, ',', ' ') }} F</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="text-align: center;">{{ $ecriture->ordre }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endforeach

        <!-- Résumé des totaux -->
        <div class="total-section">
            <div class="total-row">
                <span class="total-label">Nombre d'écritures:</span>
                <span class="total-value">{{ $entryCount }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">Total Débits:</span>
                <span class="total-value" style="color: #dc3545;">{{ number_format($totalDebit, 0, ',', ' ') }} F</span>
            </div>
            <div class="total-row">
                <span class="total-label">Total Crédits:</span>
                <span class="total-value" style="color: #28a745;">{{ number_format($totalCredit, 0, ',', ' ') }} F</span>
            </div>
            <div class="total-row" style="border-top: 1px solid #ddd; padding-top: 5px; margin-top: 5px;">
                <span class="total-label">Équilibre:</span>
                @if($totalDebit == $totalCredit)
                    <span class="total-value" style="color: #28a745;">
                        ✓ Équilibré ({{ number_format(abs($totalDebit - $totalCredit), 0, ',', ' ') }} F)
                    </span>
                @else
                    <span class="total-value" style="color: #dc3545;">
                        ⚠ Déséquilibré ({{ number_format(abs($totalDebit - $totalCredit), 0, ',', ' ') }} F)
                    </span>
                @endif
            </div>
        </div>
    @else
        <div class="no-data">
            Aucune écriture comptable trouvée pour la période sélectionnée.
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        {{ $entreprise->nom }} - Journal Comptable - Page <span class="pagenum"></span>
    </div>
</body>
</html>
