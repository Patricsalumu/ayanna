<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size:12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding:6px 8px; border: 1px solid #ddd; }
        th { background:#f5f5f5; text-align:left; }
        .right { text-align:right; }
        .center { text-align:center; }
        h1,h2 { margin:0; }
        .header { margin-bottom:12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Mouvements - {{ $pointDeVente->nom }}</h1>
        <p>Période : @if($dateFrom) {{ $dateFrom }} @else - @endif à @if($dateTo) {{ $dateTo }} @else - @endif</p>
        @if($q)
            <p>Recherche : "{{ $q }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Compte</th>
                <th>Libellé</th>
                <th class="center">Type</th>
                <th class="right">Montant</th>
                <th class="center">Annulé</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mouvements as $mvt)
                <tr>
                    <td class="center">{{ $mvt->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $mvt->compte->nom ?? 'N/A' }} ({{ $mvt->compte->numero ?? '' }})</td>
                    <td>{{ $mvt->libele }}</td>
                    <td class="center">{{ ucfirst($mvt->type) }}</td>
                    <td class="right">{{ optional($entreprise ?? auth()->user()?->entreprise)->formatAmount($mvt->montant, true, 2) }}</td>
                    <td class="center">{{ $mvt->annule ? 'Oui' : 'Non' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="right">Total Entrées</th>
                <th class="right">{{ optional($entreprise ?? auth()->user()?->entreprise)->formatAmount($totalEntree, true, 2) }}</th>
                <th></th>
            </tr>
            <tr>
                <th colspan="4" class="right">Total Sorties</th>
                <th class="right">{{ optional($entreprise ?? auth()->user()?->entreprise)->formatAmount($totalSortie, true, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
