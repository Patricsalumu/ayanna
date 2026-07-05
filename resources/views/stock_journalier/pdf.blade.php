<div style="width:100%; font-family: Arial, sans-serif;">
    <div style="text-align:center; font-size:11px; color:#6b7280; margin-bottom:6px;">
        Généré par Ayanna le {{ date('d/m/Y H:i') }}
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div>
            @if(isset($pointDeVente) && $pointDeVente->entreprise && $pointDeVente->entreprise->logo)
                <img src="{{ public_path('storage/'.$pointDeVente->entreprise->logo) }}" alt="Logo" style="height:56px; margin-bottom:4px;"><br>
            @endif
            @if(isset($pointDeVente) && $pointDeVente->entreprise)
                <div style="font-size:16px; font-weight:bold; color:#111827;">{{ $pointDeVente->entreprise->nom }}</div>
                <div style="font-size:11px; color:#374151;">{{ $pointDeVente->entreprise->adresse ?? '' }}</div>
                <div style="font-size:11px; color:#374151;">{{ $pointDeVente->entreprise->telephone ?? '' }}</div>
            @endif
        </div>
        <div style="text-align:right; max-width:320px;">
            <div style="font-size:20px; font-weight:bold; color:#1d4ed8; margin-bottom:6px;">Fiche de stock journalier</div>
            <div style="font-size:12px; color:#374151;">Date : {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
            @if(isset($session) && $session)
                <div style="font-size:12px; color:#374151;">Session : <span style="font-weight:700;">@if(strlen($session) === 14 && ctype_digit($session)){{ \Carbon\Carbon::createFromFormat('YmdHis', $session)->format('d/m/Y H:i:s') }}@else{{ $session }}@endif</span></div>
            @endif
            @if(isset($heureOuverture))
                <div style="font-size:12px; color:#374151;">Ouverture : {{ $heureOuverture->format('H:i:s') }}</div>
            @endif
            @if(isset($heureFermeture))
                <div style="font-size:12px; color:#374151;">Fermeture : {{ $heureFermeture->format('H:i:s') }}</div>
            @elseif(isset($sessionEnCours) && $sessionEnCours)
                <div style="font-size:12px; color:#b91c1c; font-weight:700;">Fermeture : en cours</div>
            @endif
        </div>
    </div>

    <div style="margin-bottom:16px; padding:12px 14px; border:1px solid #dbeafe; border-radius:12px; background:#eff6ff; color:#1e40af; font-size:13px;">
        <div style="font-weight:700; margin-bottom:6px;">Point de vente : {{ $nomPointDeVente ?? 'N/D' }}</div>
        <div style="font-size:12px; color:#1f2937;">Comptoiriste : {{ auth()->user()->name ?? 'N/D' }}</div>
    </div>

    <table style="width:100%; border-collapse:collapse; font-size:13px; color:#111827;">
        <thead>
            <tr style="background:#eff6ff; color:#1d4ed8;">
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:left;">Produit</th>
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:center;">Q. Initiale</th>
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:center;">Q. Ajoutée</th>
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:center;">Q. Totale</th>
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:center;">Q. Vendue</th>
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:center;">Q. Restée</th>
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:right;">Prix unitaire</th>
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($produitsByCategory as $categorie => $produits)
            <tr style="background:#e0f2fe; color:#0f172a;">
                <td colspan="8" style="padding:10px 12px; border:1px solid #bfdbfe; font-weight:700; font-size:14px;">{{ $categorie }}</td>
            </tr>
            @foreach($produits as $produit)
                <tr>
                    <td style="padding:8px; border:1px solid #dbeafe;">{{ $produit['nom'] }}</td>
                    <td style="padding:8px; border:1px solid #dbeafe; text-align:center;">{{ $produit['q_init'] }}</td>
                    <td style="padding:8px; border:1px solid #dbeafe; text-align:center;">{{ $produit['q_ajout'] }}</td>
                    <td style="padding:8px; border:1px solid #dbeafe; text-align:center;">{{ $produit['q_total'] }}</td>
                    <td style="padding:8px; border:1px solid #dbeafe; text-align:center;">{{ $produit['q_vendue'] }}</td>
                    <td style="padding:8px; border:1px solid #dbeafe; text-align:center;">{{ $produit['q_reste'] }}</td>
                    <td style="padding:8px; border:1px solid #dbeafe; text-align:right;">{{ number_format($produit['prix'], 0, ',', ' ') }} $</td>
                    <td style="padding:8px; border:1px solid #dbeafe; text-align:right; font-weight:700;">{{ number_format($produit['total'], 0, ',', ' ') }} $</td>
                </tr>
            @endforeach
            <tr style="background:#dbeafe; color:#0f172a; font-weight:700;">
                <td colspan="7" style="padding:10px 12px; border:1px solid #bfdbfe; text-align:right;">Total catégorie {{ $categorie }}</td>
                <td style="padding:10px 12px; border:1px solid #bfdbfe; text-align:right;">{{ number_format($categoryTotals[$categorie] ?? 0, 0, ',', ' ') }} $</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:center; padding:14px 16px; border:1px solid #bfdbfe; border-radius:12px; background:#eff6ff;">
        <div style="font-size:13px; color:#1f2937;">Total catégories : {{ count($produitsByCategory) }}</div>
        <div style="font-size:16px; font-weight:700; color:#1d4ed8;">Total vente session : {{ number_format($totalVente ?? 0, 0, ',', ' ') }} $</div>
    </div>
</div>
