<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Fiche stock 80mm</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size:9px; margin:0; padding:0; }
    .container { width: 210px; margin: 0; padding: 0 4px; }
    .header { text-align:center; margin-bottom:5px; }
    .small { font-size:8.5px; }
    table { width:100%; border-collapse: collapse; table-layout: fixed; margin:0; }
    th, td { padding: 2px 1px; font-size:8.4px; line-height:1.15; border:1px solid #000; }
    th { text-align:left; font-weight:700; background:#e5e7eb; }
    .right { text-align:right; }
    .sep { border-top:1px dashed #000; margin:4px 0; }
    .category { font-weight:700; }
    .total { font-weight:700; text-align:right; margin-top:5px; }
    .category-row td { padding-top:4px; padding-bottom:3px; }
    .product-name { word-break: break-word; white-space: normal; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="small">{{ $entreprise->nom ?? '' }}</div>
      <div class="small">Fiche stock @if($session) #{{ substr($session,0,6) }} @endif</div>
      <div class="small">{{ 
        isset($session) && strlen($session)===14 && ctype_digit($session)
          ? \Illuminate\Support\Carbon::createFromFormat('YmdHis',$session)->format('d-m H:i')
          : ($sessionLabel ?? '')
      }}</div>
    </div>

    <div class="sep"></div>

    <table>
      <thead>
        <tr>
          <th style="width:36%">Produit</th>
          <th style="width:9%" class="right">QI</th>
          <th style="width:9%" class="right">QA</th>
          <th style="width:9%" class="right">QV</th>
          <th style="width:9%" class="right">QR</th>
          <th style="width:28%" class="right">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($produitsByCategory as $categorie => $produits)
          <tr class="category-row">
            <td colspan="5" class="category">{{ $categorie }}</td>
            <td class="right category">{{ number_format($categoryTotals[$categorie] ?? 0, 0, ',', ' ') }}</td>
          </tr>
          @foreach($produits as $p)
            <tr>
              <td class="product-name" style="vertical-align:top;">{{ $p['nom'] }}</td>
              <td class="right">{{ $p['q_init'] ?? 0 }}</td>
              <td class="right">{{ $p['q_ajout'] ?? 0 }}</td>
              <td class="right">{{ $p['q_vendue'] ?? 0 }}</td>
              <td class="right">{{ $p['q_reste'] ?? 0 }}</td>
              <td class="right">{{ number_format($p['total'] ?? 0, 0, ',', ' ') }}</td>
            </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>

    <table style="margin-top:5px;">
      <tbody>
        <tr><td>Total vente session</td><td class="right" style="font-weight:700;">{{ number_format($totalVente ?? 0, 0, ',', ' ') }}</td></tr>
        <tr><td>Remises</td><td class="right">{{ number_format($totalRemise ?? 0, 0, ',', ' ') }}</td></tr>
        <tr><td>Créances</td><td class="right">{{ number_format($totalCreance ?? 0, 0, ',', ' ') }}</td></tr>
        <tr><td>Offre</td><td class="right">{{ number_format($totalOffre ?? 0, 0, ',', ' ') }}</td></tr>
        <tr><td style="font-weight:700;">Solde net</td><td class="right" style="font-weight:700;">{{ number_format($soldeNet ?? 0, 0, ',', ' ') }}</td></tr>
      </tbody>
    </table>

    <table style="margin-top:5px;">
      <thead><tr><th>Mode de paiement</th><th class="right">Montant</th></tr></thead>
      <tbody>
      @foreach(($totauxParModePaiement ?? collect()) as $mode => $val)
        <tr><td>{{ $mode }}</td><td class="right">{{ number_format($val, 0, ',', ' ') }} FC</td></tr>
      @endforeach
      </tbody>
    </table>
  </div>
</body>
</html>
