<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Inventaire d'ouverture 80mm</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; margin: 0; padding: 0; }
    .container { width: 210px; margin: 0; padding: 0 4px; }
    .header { text-align: center; margin-bottom: 5px; }
    .small { font-size: 8.5px; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { padding: 2px 1px; font-size: 8.4px; line-height: 1.15; }
    th { text-align: left; font-weight: 700; }
    .right { text-align: right; }
    .center { text-align: center; }
    .sep { border-top: 1px dashed #000; margin: 4px 0; }
    .category { font-weight: 700; }
    .product-name { width: 52%; word-break: break-word; white-space: normal; }
    .quantity { width: 16%; }
    .difference { width: 16%; }
    .total { font-weight: 700; text-align: right; margin-top: 5px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="small">{{ $pointDeVente->nom ?? $nomPointDeVente ?? '' }}</div>
      <div class="small">Inventaire d'ouverture</div>
      <div class="small">{{ $date ? \Carbon\Carbon::parse($date)->format('d-m-Y') : '' }}</div>
      @if($session)
        <div class="small">{{ $sessionLabel ?? $session }}</div>
      @endif
    </div>

    <div class="sep"></div>

    <table>
      <thead>
        <tr>
          <th class="product-name">Produit</th>
          <th class="quantity center">Système</th>
          <th class="quantity center">Compté</th>
          <th class="difference right">Écart</th>
        </tr>
      </thead>
      <tbody>
      @foreach($produitsByCategory as $categorie => $produits)
        <tr>
          <td colspan="4" class="category" style="padding-top:4px;">{{ $categorie }}</td>
        </tr>
        @foreach($produits as $produit)
          <tr>
            <td class="product-name">{{ $produit['nom'] }}</td>
            <td class="quantity center">{{ $produit['q_system'] }}</td>
            <td class="quantity center">{{ $produit['q_counted'] }}</td>
            <td class="difference right">{{ $produit['difference'] }}</td>
          </tr>
        @endforeach
        <tr><td colspan="4" class="sep"></td></tr>
      @endforeach
      </tbody>
    </table>

    <div class="total">Écart total : {{ number_format($totalDifference ?? 0, 0, ',', ' ') }}</div>
  </div>
</body>
</html>
