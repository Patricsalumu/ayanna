<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte de Résultat - {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</title>
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
            border-bottom: 2px solid #059669;
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
            color: #059669;
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
            font-size: 24px;
            font-weight: bold;
            color: #059669;
            margin: 0 0 5px 0;
        }
        
        .subtitle {
            color: #666;
            font-size: 14px;
            margin: 0 0 10px 0;
        }
        
        .generated-by {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
            margin-top: 15px;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        
        .resultat-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .resultat-section {
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
        
        .charges-header {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .produits-header {
            background: #f0fdf4;
            color: #16a34a;
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
        
        .montant.charges {
            color: #dc2626;
        }
        
        .montant.produits {
            color: #16a34a;
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
        
        .resultat-final {
            background: #f8f9fa;
            border: 2px solid #059669;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }
        
        .resultat-final.benefice {
            background: #f0fdf4;
            border-color: #16a34a;
            color: #16a34a;
        }
        
        .resultat-final.perte {
            background: #fef2f2;
            border-color: #dc2626;
            color: #dc2626;
        }
        
        .resultat-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .resultat-montant {
            font-size: 20px;
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
        
        <!-- Titre et période au centre -->
        <div class="title-section">
            <h1 class="report-title">COMPTE DE RÉSULTAT</h1>
            <p class="subtitle">Du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
            
            <!-- Mention générée par Ayanna -->
            <div class="generated-by">
                <strong>📄 Généré par Ayanna</strong><br>
                Le {{ now()->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

    <!-- Contenu du compte de résultat -->
    <div class="resultat-container">
        <!-- CHARGES -->
        <div class="resultat-section">
            <div class="section-header charges-header">
                CHARGES (Classe 6)
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60%">Compte</th>
                        <th style="width: 40%">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($charges as $compte)
                        @if($compte->montant > 0)
                        <tr>
                            <td>
                                <strong>{{ $compte->numero }}</strong><br>
                                <small>{{ $compte->nom }}</small>
                            </td>
                            <td class="montant charges">
                                {{ number_format($compte->montant, 0, ',', ' ') }} FC
                            </td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="total-row">
                        <td><strong>TOTAL CHARGES</strong></td>
                        <td class="montant charges">
                            {{ number_format($totalCharges, 0, ',', ' ') }} FC
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PRODUITS -->
        <div class="resultat-section">
            <div class="section-header produits-header">
                PRODUITS (Classe 7)
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60%">Compte</th>
                        <th style="width: 40%">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produits as $compte)
                        @if($compte->montant > 0)
                        <tr>
                            <td>
                                <strong>{{ $compte->numero }}</strong><br>
                                <small>{{ $compte->nom }}</small>
                            </td>
                            <td class="montant produits">
                                {{ number_format($compte->montant, 0, ',', ' ') }} FC
                            </td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="total-row">
                        <td><strong>TOTAL PRODUITS</strong></td>
                        <td class="montant produits">
                            {{ number_format($totalProduits, 0, ',', ' ') }} FC
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Résultat final -->
    <div class="resultat-final {{ $resultat >= 0 ? 'benefice' : 'perte' }}">
        <div class="resultat-title">
            {{ $resultat >= 0 ? '🎉 RÉSULTAT BÉNÉFICIAIRE' : '⚠ RÉSULTAT DÉFICITAIRE' }}
        </div>
        <div class="resultat-montant">
            {{ number_format(abs($resultat), 0, ',', ' ') }} FC
        </div>
        <div style="font-size: 12px; margin-top: 8px;">
            @if($resultat >= 0)
                L'entreprise dégage un bénéfice sur la période
            @else
                L'entreprise enregistre une perte sur la période
            @endif
        </div>
    </div>

    <!-- Analyse rapide -->
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h3 style="color: #059669; margin: 0 0 10px 0; font-size: 14px;">📊 Analyse de performance</h3>
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span>Total des produits :</span>
            <strong style="color: #16a34a;">{{ number_format($totalProduits, 0, ',', ' ') }} FC</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span>Total des charges :</span>
            <strong style="color: #dc2626;">{{ number_format($totalCharges, 0, ',', ' ') }} FC</strong>
        </div>
        <div style="display: flex; justify-content: space-between; border-top: 1px solid #dee2e6; padding-top: 8px;">
            <span><strong>Résultat net :</strong></span>
            <strong class="{{ $resultat >= 0 ? 'produits' : 'charges' }}">
                {{ $resultat >= 0 ? '+' : '-' }} {{ number_format(abs($resultat), 0, ',', ' ') }} FC
            </strong>
        </div>
        @if($totalProduits > 0)
        <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 10px; color: #666;">
            <span>Marge nette :</span>
            <span>{{ number_format(($resultat / $totalProduits) * 100, 1) }}%</span>
        </div>
        @endif
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Compte de résultat</strong> - 
            {{ $charges->where('montant', '>', 0)->count() }} poste(s) de charges, {{ $produits->where('montant', '>', 0)->count() }} poste(s) de produits - 
            Période du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        </p>
        <p>Document généré le {{ now()->format('d/m/Y à H:i:s') }}</p>
    </div>
</body>
</html>
