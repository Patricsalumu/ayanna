<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #111827;
            font-size: 12px;
        }

        .header {
            margin-bottom: 14px;
        }

        .generated {
            text-align: center;
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 8px;
        }

        .company {
            float: left;
            width: 45%;
        }

        .title {
            float: right;
            width: 50%;
            text-align: right;
        }

        .clear {
            clear: both;
        }

        h1 {
            margin: 0 0 6px;
            color: #2563eb;
            font-size: 22px;
        }

        .muted {
            color: #6b7280;
        }

        .summary {
            width: 100%;
            margin: 12px 0 16px;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #bfdbfe;
            padding: 10px;
            background: #eff6ff;
            font-weight: bold;
        }

        .summary .amount {
            text-align: right;
            color: #047857;
            font-size: 16px;
        }

        table.list {
            width: 100%;
            border-collapse: collapse;
        }

        .list th {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
            padding: 7px;
            text-align: left;
        }

        .list td {
            border: 1px solid #d1d5db;
            padding: 7px;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .footer-total td {
            background: #f0fdf4;
            font-weight: bold;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="generated">Genere par Ayanna le {{ now()->format('d/m/Y H:i') }}</div>
        <div class="company">
            @if(isset($entreprise) && $entreprise?->logo)
                <img src="{{ public_path('storage/'.$entreprise->logo) }}" alt="Logo" style="height:55px; margin-bottom:4px;"><br>
            @endif
            @if(isset($entreprise) && $entreprise)
                <strong>{{ $entreprise->nom }}</strong><br>
                <span class="muted">{{ $entreprise->adresse ?? '' }}</span><br>
                <span class="muted">{{ $entreprise->telephone ?? '' }}</span>
            @endif
        </div>
        <div class="title">
            <h1>Paniers de session</h1>
            <div class="muted">
                Session :
                @if($selectedSession === 'all')
                    Toutes les sessions
                @else
                    {{ $selectedSession ?? 'Jour courant' }}
                @endif
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <table class="summary">
        <tr>
            <td>Total paniers : {{ number_format($totalPaniers, 0, ',', ' ') }}</td>
            <td class="amount">Total montants : {{ number_format($totalMontants, 0, ',', ' ') }} $</td>
        </tr>
    </table>

    <table class="list">
        <thead>
            <tr>
                <th>Table</th>
                <th>Serveuse</th>
                <th>Client</th>
                <th>Point de vente</th>
                <th>Ouvert a</th>
                <th>Statut</th>
                <th class="right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paniers as $panier)
                @php
                    $montant = $panier->produits->sum(fn($p) => max(0, $p->pivot->quantite) * $p->prix_vente);
                @endphp
                <tr>
                    <td>{{ $panier->tableResto->numero ?? $panier->table_id }}</td>
                    <td>{{ $panier->serveuse->name ?? '-' }}</td>
                    <td>{{ $panier->client->nom ?? '-' }}</td>
                    <td>{{ $panier->pointDeVente->nom ?? 'N/A' }}</td>
                    <td>{{ $panier->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $panier->status }}</td>
                    <td class="right">{{ number_format($montant, 0, ',', ' ') }} $</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center muted">Aucun panier trouve</td>
                </tr>
            @endforelse
            <tr class="footer-total">
                <td colspan="6" class="right">Total</td>
                <td class="right">{{ number_format($totalMontants, 0, ',', ' ') }} $</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
