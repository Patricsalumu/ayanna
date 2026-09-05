<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recu paiement #{{ $commande->id }}</title>
    <style>
        html, body { margin: 0; padding: 0; background: #fff; color: #111; }
        body { display: flex; justify-content: center; font-family: monospace; }
        .ticket { width: 75mm; padding: 0; margin: 0; box-sizing: border-box; font-weight: bold; }
        .center { text-align: center; }
        .line { border-top: 1px solid #111; margin: 8px 0; }
        .row { font-size: 15px; color: #111; font-weight: bold; }
        .title { font-size: 20px; letter-spacing: 0.5px; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; font-size: 15px; color: #111; }
        th { border-bottom: 1px solid #111; padding: 2px 0; }
        td { padding: 2px 0; border-bottom: 1px solid rgba(17, 17, 17, 0.4); }
        .left { text-align: left; word-break: break-all; }
        .right { text-align: right; }
        .mid { text-align: center; }
        .total { text-align: right; font-size: 16px; }
        .total-main { text-align: right; font-size: 20px; }
        .small { font-size: 13px; }
        .no-print { margin: 14px 0; text-align: center; font-family: Arial, sans-serif; }
        .btn { display: inline-block; padding: 8px 12px; border-radius: 6px; text-decoration: none; background: #2563eb; color: #fff; font-size: 13px; }
        @media print {
            .no-print { display: none; }
            body { width: 75mm !important; }
        }
    </style>
</head>
<body>
    @php
        $entreprise = $commande->panier->pointDeVente->entreprise ?? null;
        $devise = $entreprise->devise ?? '$';
        $modeRaw = $commande->mode_paiement ?? $commande->panier->mode_paiement ?? 'compte_client';
        $modeNorm = strtolower(str_replace([' ', '-', 'é', 'è', 'ê', 'à'], ['_', '_', 'e', 'e', 'e', 'a'], $modeRaw));
        $modeLabel = match ($modeNorm) {
            'mobile_money', 'mobilemoney', 'mobile' => 'Mobile Money',
            'carte', 'card' => 'Carte',
            'offre' => 'Offre',
            'compte_client', 'compteclient', 'credit' => 'Compte Client',
            default => 'Especes',
        };

        $montantTotal = 0.0;
        foreach ($commande->panier->produits as $produit) {
            $montantTotal += ((float) $produit->pivot->quantite) * ((float) ($produit->pivot->prix ?? $produit->prix_vente ?? 0));
        }
        $remise = (float) ($commande->panier->total_remise ?? $commande->panier->remise ?? 0);
        $netAPayer = max(0, $montantTotal - $remise);
        $paiementsReels = $commande->paiements->filter(function ($paiement) {
            $mode = strtolower(str_replace([' ', '-', 'é', 'è', 'ê', 'à'], ['_', '_', 'e', 'e', 'e', 'a'], (string) ($paiement->mode ?? '')));
            return !in_array($mode, ['compte_client', 'compteclient', 'credit'], true);
        });

        $montantPayeBrut = (float) $paiementsReels->sum('montant');
        $montantPaye = max(0, min($netAPayer, $montantPayeBrut));
        $montantRestant = max(0, $netAPayer - $montantPaye);

        if ($montantPaye <= 0.00001) {
            $statutLabel = 'NON PAYE';
        } elseif ($montantPaye < $netAPayer) {
            $statutLabel = 'PARTIEL';
        } else {
            $statutLabel = 'PAYE';
        }
    @endphp

    <div class="ticket">
        <div class="center title">RECU DE PAIEMENT</div>
        @if($entreprise && $entreprise->logo)
            <div class="center">
                <img src="{{ asset('storage/'.$entreprise->logo) }}" alt="Logo" style="max-width:56px;max-height:56px;margin-bottom:6px;display:block;margin-left:auto;margin-right:auto;">
            </div>
        @endif
        <div class="center" style="font-size:20px;">{{ $entreprise->nom ?? 'Ayanna' }}</div>
        @if($entreprise?->numero_entreprise)
            <div class="center row">N° Entreprise : {{ $entreprise->numero_entreprise }}</div>
        @endif
        @if($entreprise?->email)
            <div class="center row">{{ $entreprise->email }}</div>
        @endif
        @if($entreprise?->telephone)
            <div class="center row">{{ $entreprise->telephone }}</div>
        @endif
        @if($entreprise?->adresse)
            <div class="center row">{{ $entreprise->adresse }}</div>
        @endif

        <div class="line"></div>
        <div class="row">Facture n° <b>{{ $commande->id }}</b></div>
        <div class="row">Client : <b>{{ $commande->panier->client->nom ?? '-' }}</b></div>
        <div class="row">Serveuse : <b>{{ $commande->panier->serveuse->name ?? '-' }}</b></div>
        <div class="row">Table : <b>{{ $commande->panier->tableResto->numero ?? $commande->panier->table_id }}</b> | Panier n° <b>{{ $commande->panier->id }}</b></div>
        <div class="row">Mode de paiement : <b>{{ $modeLabel }}</b></div>
        <div class="row">Etat paiement : <b>{{ $statutLabel }}</b></div>
        <div class="row">Date : <b>{{ \Carbon\Carbon::parse($commande->created_at)->format('d/m/Y H:i') }}</b></div>
        <div class="line"></div>

        <table>
            <thead>
                <tr>
                    <th class="left">Produit</th>
                    <th class="mid">Qte</th>
                    <th class="right">Prix</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commande->panier->produits as $produit)
                    @php
                        $qte = (float) $produit->pivot->quantite;
                        $prix = (float) ($produit->pivot->prix ?? $produit->prix_vente ?? 0);
                        $ligne = $qte * $prix;
                    @endphp
                    <tr>
                        <td class="left">{{ $produit->nom }}</td>
                        <td class="mid">{{ number_format($qte, 0, ',', ' ') }}</td>
                        <td class="right">{{ number_format($prix, 2, ',', ' ') }} {{ $devise }}</td>
                        <td class="right">{{ number_format($ligne, 2, ',', ' ') }} {{ $devise }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="line"></div>
        <div class="total">Sous-total : {{ number_format($montantTotal, 2, ',', ' ') }} {{ $devise }}</div>
        <div class="total">Remise : {{ number_format($remise, 2, ',', ' ') }} {{ $devise }}</div>
        <div class="total-main">Net a payer : {{ number_format($netAPayer, 2, ',', ' ') }} {{ $devise }}</div>
        <div class="total">Montant paye : {{ number_format($montantPaye, 2, ',', ' ') }} {{ $devise }}</div>
        <div class="total">Reste du : {{ number_format($montantRestant, 2, ',', ' ') }} {{ $devise }}</div>

        <div class="center row" style="margin-top:12px;">Merci pour votre visite !</div>
        <div class="center small" style="margin-top:10px;">Genere par Ayanna | {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    @unless($autoPrint ?? false)
        <div class="no-print">
            <button onclick="window.print()" class="btn">Imprimer</button>
        </div>
    @endunless

    @if($autoPrint ?? false)
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 150);
            });
            window.addEventListener('afterprint', function () {
                window.close();
            });
        </script>
    @endif
</body>
</html>
