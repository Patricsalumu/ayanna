<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mouvements du compte {{ $compte->numero }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #aaa; padding: 6px 8px; text-align: left; }
        th { background: #e3e3f7; }
        .credit { color: #228B22; }
        .debit { color: #B22222; }
    </style>
</head>
<body>
    <div style="text-align:center; font-size:12px; color:#888; margin-bottom:4px;">
        Généré par Ayanna &copy; {{ date('d/m/Y H:i') }}
    </div>
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
        <div style="text-align:left;">
            @if(isset($compte->entreprise) && $compte->entreprise->logo)
                <img src="{{ public_path('storage/'.$compte->entreprise->logo) }}" alt="Logo" style="height:60px; margin-bottom:4px;"><br>
            @endif
            @if(isset($compte->entreprise))
                <span style="font-weight:bold; font-size:15px;">{{ $compte->entreprise->nom }}</span><br>
                <span style="font-size:12px;">{{ $compte->entreprise->adresse ?? '' }}</span><br>
                <span style="font-size:12px;">{{ $compte->entreprise->telephone ?? '' }}</span>
            @endif
        </div>
        <div style="text-align:right;">
            <span style="font-weight:bold; font-size:14px; color:#2563eb;">Compte n° : {{ $compte->numero }}</span><br>
        </div>
    </div>
    <h2 style="text-align:center; color:#2563eb;">Mouvements du compte {{ $compte->numero }} ({{ $compte->nom }})</h2>
    <p>
        <strong>Filtre :</strong>
        @if($date) Date = {{ $date }} @endif
        @if($type) | Type = {{ ucfirst($type) }} @endif
        @if($search) | Recherche = "{{ $search }}" @endif
    </p>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Montant</th>
                <th>Type</th>
                <th>Libellé</th>
            </tr>
        </thead>
        <tbody>
        @forelse($mouvements as $mvt)
            <tr>
                <td>{{ $mvt->created_at->format('d/m/Y H:i') }}</td>
                <td class="{{ $mvt->type }}">{{ number_format($mvt->montant, 2, ',', ' ') }} F</td>
                <td>{{ ucfirst($mvt->type) }}</td>
                <td>{{ $mvt->libele }}</td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center; color:#888;">Aucun mouvement</td></tr>
        @endforelse
        </tbody>
    </table>
    <p style="margin-top:20px;">
        <strong>Total crédits :</strong> {{ number_format($totalCredit, 2, ',', ' ') }} F<br>
        <strong>Total débits :</strong> {{ number_format($totalDebit, 2, ',', ' ') }} F<br>
        <strong>Solde courant :</strong> {{ number_format($totalCredit - $totalDebit, 2, ',', ' ') }} F
    </p>
</body>
</html>
