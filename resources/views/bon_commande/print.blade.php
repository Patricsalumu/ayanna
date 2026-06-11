<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Commande #{{ $bon->numero_bon }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            width: 80mm;
            background: white;
            margin: 0 auto;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            line-height: 1.2;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
            border-bottom: 2px dashed #000;
            padding-bottom: 3px;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .bon-title {
            font-size: 12px;
            font-weight: bold;
            margin: 2px 0;
        }

        .serveuse-info {
            text-align: center;
            font-weight: bold;
            margin: 3px 0;
            font-size: 12px;
        }

        .details {
            font-size: 10px;
            margin: 2px 0;
            text-align: center;
        }

        .separator {
            border-bottom: 1px dashed #000;
            margin: 3px 0;
        }

        .produits {
            margin: 5px 0;
        }

        .produit-item {
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .produit-item:last-child {
            border-bottom: none;
        }

        .produit-nom {
            flex: 1;
            font-size: 13px;
            font-weight: bold;
        }

        .produit-quantite {
            text-align: right;
            font-weight: bold;
            margin-left: 5px;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            margin-top: 5px;
            font-size: 9px;
            border-top: 2px dashed #000;
            padding-top: 3px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                width: 100%;
                padding: 5px;
                box-shadow: none;
                margin: 0;
                page-break-after: avoid;
            }
            .print-button {
                display: none;
            }
        }

        .print-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ config('app.name', 'Ayanna') }}</div>
        </div>

        <div class="bon-title">BON DE COMMANDE No {{ $bon->numero_bon }}</div>

        <div class="serveuse-info">{{ $bon->serveuse?->name ?? 'N/A' }} | Table {{ $bon->panier?->table_id ?? 'N/A' }}</div>

        <div class="details">
            Panier #{{ $bon->panier_id }} | {{ $bon->created_at->format('H:i') }}
        </div>

        <div class="separator"></div>

        <div class="produits">
            @php
                $produits = is_string($bon->produits_json) ? json_decode($bon->produits_json, true) : $bon->produits_json;
            @endphp
            @if($produits && count($produits) > 0)
                @foreach($produits as $produit)
                    <div class="produit-item">
                        <span class="produit-nom">{{ $produit['nom'] }}</span>
                        <span class="produit-quantite">x{{ $produit['quantite'] }}</span>
                    </div>
                @endforeach
            @else
                <div style="text-align: center; color: #999; font-size: 10px;">
                    Aucun produit
                </div>
            @endif
        </div>

        <div class="separator"></div>

        <div class="footer">
            {{ now()->format('H:i') }}
        </div>
    </div>

    <button class="print-button" onclick="window.print(); return false;">
        🖨️ Imprimer
    </button>
</body>
</html>
