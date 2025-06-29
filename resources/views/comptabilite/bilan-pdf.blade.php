<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilan Comptable - {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</title>
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
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4F46E5;
        }
        
        .company-info {
            flex: 1;
            max-width: 40%;
        }
        
        .company-logo {
            max-height: 60px;
            max-width: 120px;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        
        .company-details {
            font-size: 10px;
            color: #666;
            line-height: 1.4;
        }
        
        .title-section {
            flex: 1;
            text-align: center;
        }
        
        .report-title {
            color: #4F46E5;
            font-size: 24px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        
        .subtitle {
            color: #666;
            font-size: 14px;
            margin: 0 0 15px 0;
        }
        
        .generated-by {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
            font-size: 10px;
            color: #666;
        }
        
        .bilan-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .bilan-section {
            flex: 1;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .section-header {
            background: #f8f9fa;
            color: #495057;
            font-weight: bold;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }
        
        .actif-header {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .passif-header {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        
        th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
            padding: 6px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-size: 9px;
        }
        
        td {
            padding: 5px 6px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .montant {
            text-align: right;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #e9ecef !important;
            font-weight: bold;
            border-top: 2px solid #495057;
        }
        
        .total-row td {
            border-top: 2px solid #495057;
            font-weight: bold;
            font-size: 11px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }
        
        .equilibre-check {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .equilibre-warning {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- En-tête avec logo et informations entreprise -->
    <div class="header-container">
        <!-- Logo et informations entreprise à gauche -->
        <div class="company-info">
            @if(Auth::user()->entreprise && Auth::user()->entreprise->logo)
                <img src="{{ public_path('storage/' . Auth::user()->entreprise->logo) }}" 
                     alt="Logo" 
                     class="company-logo">
            @endif
            <div class="company-name">
                {{ Auth::user()->entreprise->nom ?? 'Entreprise' }}
            </div>
            <div class="company-details">
                @if(Auth::user()->entreprise->numero_entreprise)
                    N° Entreprise : {{ Auth::user()->entreprise->numero_entreprise }}<br>
                @endif
                @if(Auth::user()->entreprise->adresse)
                    {{ Auth::user()->entreprise->adresse }}<br>
                @endif
                @if(Auth::user()->entreprise->telephone)
                    Tél : {{ Auth::user()->entreprise->telephone }}<br>
                @endif
                @if(Auth::user()->entreprise->email)
                    Email : {{ Auth::user()->entreprise->email }}
                @endif
            </div>
        </div>
        
        <!-- Titre et date au centre -->
        <div class="title-section">
            <h1 class="report-title">BILAN COMPTABLE</h1>
            <p class="subtitle">Situation au {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
            
            <!-- Mention générée par Ayanna -->
            <div class="generated-by">
                <strong>📄 Généré par Ayanna</strong><br>
                Le {{ now()->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

    <!-- Contenu du bilan -->
    <div class="bilan-container">
        <!-- ACTIF -->
        <div class="bilan-section">
            <div class="section-header actif-header">
                ACTIF
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60%">Poste</th>
                        <th style="width: 40%">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalActif = 0; @endphp
                    @foreach($actif as $item)
                        @php $totalActif += $item['montant']; @endphp
                        <tr>
                            <td>
                                <strong>{{ $item['compte']->numero }}</strong><br>
                                <small>{{ $item['compte']->nom }}</small>
                            </td>
                            <td class="montant">
                                {{ number_format($item['montant'], 0, ',', ' ') }} FC
                            </td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><strong>TOTAL ACTIF</strong></td>
                        <td class="montant">
                            {{ number_format($totalActif, 0, ',', ' ') }} FC
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PASSIF -->
        <div class="bilan-section">
            <div class="section-header passif-header">
                PASSIF
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60%">Poste</th>
                        <th style="width: 40%">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalPassif = 0; @endphp
                    @foreach($passif as $item)
                        @php $totalPassif += $item['montant']; @endphp
                        <tr>
                            <td>
                                <strong>{{ $item['compte']->numero }}</strong><br>
                                <small>{{ $item['compte']->nom }}</small>
                            </td>
                            <td class="montant">
                                {{ number_format($item['montant'], 0, ',', ' ') }} FC
                            </td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><strong>TOTAL PASSIF</strong></td>
                        <td class="montant">
                            {{ number_format($totalPassif, 0, ',', ' ') }} FC
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Vérification d'équilibre -->
    <div class="equilibre-check {{ abs($totalActif - $totalPassif) >= 0.01 ? 'equilibre-warning' : '' }}">
        @if(abs($totalActif - $totalPassif) < 0.01)
            ✓ BILAN ÉQUILIBRÉ - Actif = Passif ({{ number_format($totalActif, 0, ',', ' ') }} FC)
        @else
            ⚠ DÉSÉQUILIBRE DÉTECTÉ - Écart de {{ number_format(abs($totalActif - $totalPassif), 0, ',', ' ') }} FC
            <br>Actif: {{ number_format($totalActif, 0, ',', ' ') }} FC - Passif: {{ number_format($totalPassif, 0, ',', ' ') }} FC
        @endif
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Bilan comptable</strong> - 
            {{ count($actif) }} poste(s) à l'actif, {{ count($passif) }} poste(s) au passif - 
            Document généré par <strong>Ayanna ©</strong> le {{ now()->format('d/m/Y à H:i') }}
        </p>
    </div>
</body>
</html>
