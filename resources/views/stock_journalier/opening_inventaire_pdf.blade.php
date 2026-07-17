<div style="width:100%; font-family: Arial, sans-serif;">
    <div style="text-align:center; font-size:11px; color:#6b7280; margin-bottom:6px;">
        Généré par Ayanna le {{ date('d/m/Y H:i') }}
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div>
            <div style="font-size:20px; font-weight:bold; color:#1d4ed8;">Inventaire d'ouverture de session</div>
            <div style="font-size:12px; color:#374151;">Point de vente : {{ $nomPointDeVente ?? 'N/D' }}</div>
            <div style="font-size:12px; color:#374151;">Date : {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
            @if(isset($session) && $session)
                <div style="font-size:12px; color:#374151;">Session : <span style="font-weight:700;">{{ $sessionLabel ?? $session }}</span></div>
            @endif
        </div>
    </div>

    <table style="width:100%; border-collapse:collapse; font-size:13px; color:#111827;">
        <thead>
            <tr style="background:#eff6ff; color:#1d4ed8;">
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:left;">Produit</th>
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:center;">Qté système</th>
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:center;">Qté comptée</th>
                <th style="padding:8px; border:1px solid #bfdbfe; text-align:center;">Écart</th>
            </tr>
        </thead>
        <tbody>
        @foreach($produitsByCategory as $categorie => $produits)
            <tr style="background:#dbeafe; color:#0f172a; font-weight:700;">
                <td colspan="4" style="padding:10px 12px; border:1px solid #bfdbfe;">{{ $categorie }}</td>
            </tr>
            @foreach($produits as $produit)
                <tr>
                    <td style="padding:8px; border:1px solid #dbeafe;">{{ $produit['nom'] }}</td>
                    <td style="padding:8px; border:1px solid #dbeafe; text-align:center;">{{ $produit['q_system'] }}</td>
                    <td style="padding:8px; border:1px solid #dbeafe; text-align:center;">{{ $produit['q_counted'] }}</td>
                    <td style="padding:8px; border:1px solid #dbeafe; text-align:center; font-weight:700;">{{ $produit['difference'] }}</td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>

    <div style="margin-top:20px; padding:14px 16px; border:1px solid #bfdbfe; border-radius:12px; background:#eff6ff; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:13px; color:#1f2937;">Candité catégories : {{ count($produitsByCategory) }}</div>
        <div style="font-size:16px; font-weight:700; color:#1d4ed8;">Écart total : {{ optional(auth()->user()?->entreprise)->formatAmount($totalDifference ?? 0, true, 2) }}</div>
    </div>
</div>
