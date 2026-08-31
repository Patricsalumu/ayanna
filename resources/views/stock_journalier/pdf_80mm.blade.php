<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fiche stock 80mm</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
            color: #111827;
        }
        .container {
            width: 220px;
            margin: 0 auto;
            padding: 5px;
        }
        .header {
            text-align: center;
            margin-bottom: 6px;
        }
        .company {
            font-weight: 700;
            font-size: 11px;
        }
        .small {
            font-size: 8px;
            color: #374151;
        }
        .separator {
            border-top: 1px dashed #111827;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            padding: 2px 1px;
            vertical-align: top;
            border: 1px solid #d1d5db;
        }
        th {
            text-align: center;
            font-weight: 700;
            font-size: 8px;
            background: #eff6ff;
        }
        .category-row td {
            background: #f3f4f6;
            font-weight: 700;
            font-size: 8px;
            padding: 3px 2px;
        }
        .right {
            text-align: right;
        }
        .center {
            text-align: center;
        }
        .name {
            font-size: 8px;
            line-height: 1.2;
        }
        .total {
            margin-top: 8px;
            font-weight: 700;
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        @php $company = $entreprise ?? ($pointDeVente->entreprise ?? null); @endphp

        <div class="header">
            <div class="company">{{ $company->nom ?? 'Ayanna' }}</div>
            <div class="small">Fiche stock
                @if(!empty($session))
                    #{{ substr($session, 0, 6) }}
                @endif
            </div>
            <div class="small">
                {{ isset($session) && strlen((string) $session) === 14 && ctype_digit((string) $session)
                    ? \Illuminate\Support\Carbon::createFromFormat('YmdHis', $session)->format('d-m H:i')
                    : ($sessionLabel ?? '') }}
            </div>
        </div>

        <div class="separator"></div>

        @php
            $totalQiGeneral = 0;
            $totalQaGeneral = 0;
            $totalVenteGeneral = 0;
            foreach ($produitsByCategory as $produits) {
                foreach ($produits as $p) {
                    $totalQiGeneral += (int) ($p['q_init'] ?? 0);
                    $totalQaGeneral += (int) ($p['q_ajout'] ?? 0);
                    $totalVenteGeneral += (float) ($p['total'] ?? 0);
                }
            }
        @endphp

        @foreach($produitsByCategory as $categorie => $produits)
            @php
                $sousTotalQi = 0;
                $sousTotalQa = 0;
                $sousTotalVente = 0;
                foreach ($produits as $p) {
                    $sousTotalQi += (int) ($p['q_init'] ?? 0);
                    $sousTotalQa += (int) ($p['q_ajout'] ?? 0);
                    $sousTotalVente += (float) ($p['total'] ?? 0);
                }
            @endphp
            <table>
                <tr class="category-row">
                    <td colspan="7">{{ $categorie }}</td>
                </tr>
                <tr>
                    <th style="width:30%;">Produit</th>
                    <th style="width:10%;">QI</th>
                    <th style="width:10%;">QA</th>
                    <th style="width:10%;">QT</th>
                    <th style="width:10%;">QV</th>
                    <th style="width:10%;">QR</th>
                    <th style="width:20%;">Total</th>
                </tr>
                @foreach($produits as $p)
                    <tr>
                        <td class="name">{{ $p['nom'] ?? '' }}</td>
                        <td class="center">{{ $p['q_init'] ?? 0 }}</td>
                        <td class="center">{{ $p['q_ajout'] ?? 0 }}</td>
                        <td class="center">{{ ($p['q_init'] ?? 0) + ($p['q_ajout'] ?? 0) }}</td>
                        <td class="center">{{ $p['q_vendue'] ?? 0 }}</td>
                        <td class="center">{{ $p['q_reste'] ?? 0 }}</td>
                        <td class="right">{{ $company?->formatAmount($p['total'] ?? 0, true, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="category-row">
                    <td colspan="1">Sous-total</td>
                    <td class="center"><strong>{{ $sousTotalQi }}</strong></td>
                    <td class="center"><strong>{{ $sousTotalQa }}</strong></td>
                    <td class="center"><strong>{{ $sousTotalQi + $sousTotalQa }}</strong></td>
                    <td class="center">-</td>
                    <td class="center">-</td>
                    <td class="right"><strong>{{ $company?->formatAmount($sousTotalVente, true, 2) }}</strong></td>
                </tr>
            </table>
            <div class="separator"></div>
        @endforeach

        <div class="separator"></div>
        <div style="font-size:9px; line-height:1.5;">
            <div>Total Qi : <strong>{{ $totalQiGeneral }}</strong></div>
            <div>Total Qa : <strong>{{ $totalQaGeneral }}</strong></div>
            <div>Total vente session : <strong>{{ $company?->formatAmount($totalVenteGeneral, true, 2) }}</strong></div>
        </div>

        <div class="total">Total général : {{ $company?->formatAmount($totalVente ?? 0, true, 2) }}</div>
    </div>
</body>
</html>
