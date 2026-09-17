<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; font-size: 9px; }
        h1 { margin: 0 0 4px; color: #047857; font-size: 18px; }
        .muted { color: #6b7280; }
        .header { margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #d1fae5; color: #065f46; border: 1px solid #9ca3af; padding: 6px 4px; text-align: center; }
        td { border: 1px solid #d1d5db; padding: 5px 4px; }
        .right { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .total td { background: #ecfdf5; font-weight: bold; color: #065f46; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport récapitulatif des sessions</h1>
        <div class="muted">
            {{ $entreprise?->nom ?? '' }} | Généré le {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @php
        $totals = [
            'total_vente' => 0,
            'total_paye' => 0,
            'total_non_paye' => 0,
            'total_remise' => 0,
            'total_offre' => 0,
            'modes' => collect($modeColumns)->mapWithKeys(fn ($mode) => [$mode['code'] => 0])->all(),
        ];
    @endphp

    <table>
        <thead>
            <tr>
                <th>Nom session</th>
                <th>Date</th>
                <th>Total vente</th>
                <th>Total payé</th>
                <th>Total non payé</th>
                <th>Espèces</th>
                <th>Remises</th>
                <th>Offres</th>
                @foreach($modeColumns as $mode)
                    @if(!in_array($mode['code'], ['especes', 'offre'], true))
                        <th>{{ $mode['nom'] }}</th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $totals['total_vente'] += $row['total_vente'];
                    $totals['total_paye'] += $row['total_paye'];
                    $totals['total_non_paye'] += $row['total_non_paye'];
                    $totals['total_remise'] += $row['total_remise'];
                    $totals['total_offre'] += $row['total_offre'];
                    foreach ($row['modes'] as $code => $value) {
                        if (array_key_exists($code, $totals['modes'])) $totals['modes'][$code] += $value;
                    }
                @endphp
                <tr>
                    <td>{{ $row['nom'] }}</td>
                    <td class="center">{{ $row['date'] }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($row['total_vente'], true, 0) }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($row['total_paye'], true, 0) }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($row['total_non_paye'], true, 0) }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($row['modes']['especes'] ?? 0, true, 0) }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($row['total_remise'], true, 0) }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($row['total_offre'], true, 0) }}</td>
                    @foreach($modeColumns as $mode)
                        @if(!in_array($mode['code'], ['especes', 'offre'], true))
                            <td class="right">{{ $entreprise?->formatAmount($row['modes'][$mode['code']] ?? 0, true, 0) }}</td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="20" class="center muted">Aucune session trouvée</td></tr>
            @endforelse
            @if(count($rows) > 0)
                <tr class="total">
                    <td colspan="2">TOTAL</td>
                    <td class="right">{{ $entreprise?->formatAmount($totals['total_vente'], true, 0) }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($totals['total_paye'], true, 0) }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($totals['total_non_paye'], true, 0) }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($totals['modes']['especes'] ?? 0, true, 0) }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($totals['total_remise'], true, 0) }}</td>
                    <td class="right">{{ $entreprise?->formatAmount($totals['total_offre'], true, 0) }}</td>
                    @foreach($modeColumns as $mode)
                        @if(!in_array($mode['code'], ['especes', 'offre'], true))
                            <td class="right">{{ $entreprise?->formatAmount($totals['modes'][$mode['code']] ?? 0, true, 0) }}</td>
                        @endif
                    @endforeach
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>