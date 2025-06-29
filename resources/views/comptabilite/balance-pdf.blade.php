<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance Comptable - {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</title>
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
            border-bottom: 2px solid #4F46E5;
        }
        
        .header h1 {
            color: #4F46E5;
            font-size: 24px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 12px;
            color: #555;
        }
        
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
        }
        
        .summary-box {
            flex: 1;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        .summary-box .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .summary-box .value {
            font-size: 16px;
            font-weight: bold;
        }
        
        .summary-box.debit .value {
            color: #dc3545;
        }
        
        .summary-box.credit .value {
            color: #28a745;
        }
        
        .summary-box.equilibre .value {
            color: #28a745;
        }
        
        .summary-box.equilibre.warning .value {
            color: #dc3545;
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
        
        tr:hover {
            background-color: #e9ecef;
        }
        
        .compte-numero {
            font-weight: bold;
            color: #495057;
        }
        
        .compte-nom {
            color: #6c757d;
            font-size: 9px;
        }
        
        .type-badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .type-actif {
            background-color: #cce5ff;
            color: #0066cc;
        }
        
        .type-passif {
            background-color: #e6ccff;
            color: #7a00cc;
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
        
        .totaux {
            background-color: #e9ecef !important;
            font-weight: bold;
            border-top: 2px solid #495057;
        }
        
        .totaux td {
            border-top: 2px solid #495057;
            font-weight: bold;
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
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #4F46E5;">
        <!-- Logo et informations entreprise à gauche -->
        <div style="flex: 1;">
            @if(Auth::user()->entreprise && Auth::user()->entreprise->logo)
                <img src="{{ public_path('storage/' . Auth::user()->entreprise->logo) }}" 
                     alt="Logo" 
                     style="max-height: 60px; max-width: 120px; margin-bottom: 10px;">
            @endif
            <div style="font-size: 12px; color: #333; line-height: 1.4;">
                @if(Auth::user()->entreprise)
                    <div style="font-weight: bold; font-size: 14px; color: #4F46E5;">{{ Auth::user()->entreprise->nom }}</div>
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
            <h1 style="color: #4F46E5; font-size: 24px; margin: 0 0 5px 0; font-weight: bold;">BALANCE COMPTABLE</h1>
            <p style="color: #666; font-size: 14px; margin: 0 0 10px 0;">État des soldes au {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
            
            <!-- Mention générée par Ayanna -->
            <div style="background: #f8f9fa; padding: 8px 12px; border-radius: 5px; border: 1px solid #e9ecef; margin-top: 15px;">
                <div style="font-size: 10px; color: #666; text-align: center;">
                    <strong>📄 Généré par Ayanna</strong><br>
                    Le {{ now()->format('d/m/Y à H:i') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé -->
    <div class="summary">
        <div class="summary-box debit">
            <div class="label">Total Débit</div>
            <div class="value">{{ number_format($totalDebit, 0, ',', ' ') }} FC</div>
        </div>
        <div class="summary-box credit">
            <div class="label">Total Crédit</div>
            <div class="value">{{ number_format($totalCredit, 0, ',', ' ') }} FC</div>
        </div>
        <div class="summary-box equilibre {{ abs($totalDebit - $totalCredit) >= 0.01 ? 'warning' : '' }}">
            <div class="label">Équilibre</div>
            @php $equilibre = $totalDebit - $totalCredit; @endphp
            <div class="value">
                {{ abs($equilibre) < 0.01 ? '✓ Équilibré' : '⚠ ' . number_format(abs($equilibre), 0, ',', ' ') . ' FC' }}
            </div>
        </div>
    </div>

    <!-- Table de la balance -->
    <table>
        <thead>
            <tr>
                <th style="width: 20%">Compte</th>
                <th style="width: 10%">Type</th>
                <th style="width: 14%">Débit Période</th>
                <th style="width: 14%">Crédit Période</th>
                <th style="width: 14%">Solde Débit</th>
                <th style="width: 14%">Solde Crédit</th>
                <th style="width: 14%">Solde Net</th>
            </tr>
        </thead>
        <tbody>
            @forelse($balance as $item)
                <tr>
                    <td>
                        <div class="compte-numero">{{ $item['compte']->numero }}</div>
                        <div class="compte-nom">{{ $item['compte']->nom }}</div>
                    </td>
                    <td>
                        <span class="type-badge {{ $item['compte']->type === 'actif' ? 'type-actif' : 'type-passif' }}">
                            {{ ucfirst($item['compte']->type) }}
                        </span>
                    </td>
                    <td class="montant {{ $item['debit_periode'] > 0 ? 'debit' : 'vide' }}">
                        {{ $item['debit_periode'] > 0 ? number_format($item['debit_periode'], 0, ',', ' ') : '-' }}
                    </td>
                    <td class="montant {{ $item['credit_periode'] > 0 ? 'credit' : 'vide' }}">
                        {{ $item['credit_periode'] > 0 ? number_format($item['credit_periode'], 0, ',', ' ') : '-' }}
                    </td>
                    <td class="montant {{ $item['solde_debit'] > 0 ? 'debit' : 'vide' }}">
                        {{ $item['solde_debit'] > 0 ? number_format($item['solde_debit'], 0, ',', ' ') : '-' }}
                    </td>
                    <td class="montant {{ $item['solde_credit'] > 0 ? 'credit' : 'vide' }}">
                        {{ $item['solde_credit'] > 0 ? number_format($item['solde_credit'], 0, ',', ' ') : '-' }}
                    </td>
                    <td class="montant {{ ($item['solde_debit'] - $item['solde_credit']) > 0 ? 'debit' : (($item['solde_debit'] - $item['solde_credit']) < 0 ? 'credit' : 'vide') }}">
                        @php $soldeNet = $item['solde_debit'] - $item['solde_credit']; @endphp
                        {{ $soldeNet != 0 ? number_format(abs($soldeNet), 0, ',', ' ') . ($soldeNet > 0 ? ' (D)' : ' (C)') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                        <strong>Aucun compte trouvé</strong><br>
                        <small>Configurez d'abord vos comptes comptables</small>
                    </td>
                </tr>
            @endforelse
            
            <!-- Ligne des totaux -->
            @if(count($balance) > 0)
                <tr class="totaux">
                    <td colspan="2"><strong>TOTAUX</strong></td>
                    <td class="montant debit">
                        {{ number_format(collect($balance)->sum('debit_periode'), 0, ',', ' ') }}
                    </td>
                    <td class="montant credit">
                        {{ number_format(collect($balance)->sum('credit_periode'), 0, ',', ' ') }}
                    </td>
                    <td class="montant debit">
                        {{ number_format($totalDebit, 0, ',', ' ') }}
                    </td>
                    <td class="montant credit">
                        {{ number_format($totalCredit, 0, ',', ' ') }}
                    </td>
                    <td class="montant {{ ($totalDebit - $totalCredit) > 0 ? 'debit' : (($totalDebit - $totalCredit) < 0 ? 'credit' : '') }}">
                        @php $soldeNetTotal = $totalDebit - $totalCredit; @endphp
                        {{ $soldeNetTotal != 0 ? number_format(abs($soldeNetTotal), 0, ',', ' ') . ($soldeNetTotal > 0 ? ' (D)' : ' (C)') : '0' }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>{{ count($balance) }} compte(s)</strong> - 
            Balance comptable générée le {{ now()->format('d/m/Y à H:i') }} - 
            Document généré par Ayanna © {{ date('Y') }}
        </p>
        @if(abs($totalDebit - $totalCredit) >= 0.01)
            <p style="color: #dc3545; font-weight: bold; margin-top: 10px;">
                ⚠ ATTENTION: La balance n'est pas équilibrée. 
                Écart de {{ number_format(abs($totalDebit - $totalCredit), 0, ',', ' ') }} FC
            </p>
        @else
            <p style="color: #28a745; font-weight: bold; margin-top: 10px;">
                ✓ Balance équilibrée - Tous les comptes sont cohérents
            </p>
        @endif
    </div>
</body>
</html>
