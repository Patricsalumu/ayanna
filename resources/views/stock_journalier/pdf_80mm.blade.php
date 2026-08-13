<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Fiche stock 80mm</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
    .container { width: 220px; margin: 0 auto; }
    .header { text-align:center; margin-bottom:8px; }
    .small { font-size:11px; }
    table { width:100%; border-collapse: collapse; }
    th, td { padding: 4px 2px; }
    th { text-align:left; font-weight:700; }
    .right { text-align:right; }
    .sep { border-top:1px dashed #000; margin:6px 0; }
    .category { font-weight:700; margin-top:6px; }
    .total { font-weight:700; text-align:right; margin-top:8px; }
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
          <th style="width:55%">Produit</th>
          <th style="width:15%" class="right">QI</th>
          <th style="width:15%" class="right">QA</th>
          <th style="width:15%" class="right">QV</th>
          <th style="width:15%" class="right">QR</th>
          <th style="width:20%" class="right">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($produitsByCategory as $categorie => $produits)
          <tr><td colspan="6" class="category">{{ $categorie }}</td></tr>
          @foreach($produits as $p)
            <tr>
              <td style="vertical-align:top">{{ $p['nom'] }}</td>
              <td class="right">{{ $p['q_init'] ?? 0 }}</td>
              <td class="right">{{ $p['q_ajout'] ?? 0 }}</td>
              <td class="right">{{ $p['q_vendue'] ?? 0 }}</td>
              <td class="right">{{ $p['q_reste'] ?? 0 }}</td>
              <td class="right">{{ number_format($p['total'] ?? 0, 0, ',', ' ') }}</td>
            </tr>
          @endforeach
          <tr><td colspan="6" class="sep"></td></tr>
        @endforeach
      </tbody>
    </table>

    <div class="total">Total vente session: {{ number_format($totalVente ?? 0, 0, ',', ' ') }}</div>

    <div class="sep"></div>
    <div class="small">Catégories imprimées:</div>
    <div class="small">
      @foreach($categoryTotals as $cat => $val)
        - {{ $cat }} : {{ number_format($val, 0, ',', ' ') }}<br/>
      @endforeach
    </div>
  </div>
</body>
</html>
