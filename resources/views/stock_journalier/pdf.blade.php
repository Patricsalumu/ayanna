<div style="width:100%; font-family: Arial, sans-serif;">
    <div style="text-align:center; font-size:12px; color:#888; margin-bottom:4px;">
        Généré par Ayanna le {{ date('d/m/Y H:i') }}
    </div>
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
        <div style="text-align:left;">
            @if(isset($pointDeVente) && $pointDeVente->entreprise && $pointDeVente->entreprise->logo)
                <img src="{{ public_path('storage/'.$pointDeVente->entreprise->logo) }}" alt="Logo" style="height:60px; margin-bottom:4px;"><br>
            @endif
            @if(isset($pointDeVente) && $pointDeVente->entreprise)
                <span style="font-weight:bold; font-size:15px;">{{ $pointDeVente->entreprise->nom }}</span><br>
                <span style="font-size:12px;">{{ $pointDeVente->entreprise->adresse ?? '' }}</span><br>
                <span style="font-size:12px;">{{ $pointDeVente->entreprise->telephone ?? '' }}</span>
            @endif
        </div>
        <div style="width:80px;"></div> <!-- Espace à droite pour équilibrer -->
    </div>
    <h1 style="font-size:22px; color:#2563eb; margin-bottom:12px;">Fiche de stock journalier du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h1>
    @if(isset($nomPointDeVente))
        <div style="margin-bottom:12px; color:#2563eb; background:#e0edff; border-radius:6px; padding:8px 16px; font-weight:bold;">
            Point de vente courant : {{ $nomPointDeVente }}<br>
            Comptoiriste : {{ auth()->user()->name ?? '' }}
            @if(isset($session) && $session)
                <br>Session : 
                @if(strlen($session) === 14 && ctype_digit($session))
                    {{ \Carbon\Carbon::createFromFormat('YmdHis', $session)->format('d/m/Y H:i:s') }}
                @else
                    {{ $session }}
                @endif
                
                <!-- Informations d'ouverture et fermeture -->
                @if(isset($heureOuverture))
                    <br>Heure d'ouverture : {{ $heureOuverture->format('H:i:s') }}
                @endif
                
                @if(isset($heureFermeture))
                    <br>Heure de fermeture : {{ $heureFermeture->format('H:i:s') }}
                @elseif(isset($sessionEnCours) && $sessionEnCours)
                    <br>Heure de fermeture : <span style="color:#dc2626; font-weight:bold;">En cours</span>
                @endif
            @endif
        </div>
    @endif
    <table style="width:100%; border-collapse:collapse; font-size:14px;">
        <thead>
            <tr style="background:#e0edff; color:#2563eb;">
                <th style="padding:6px; border:1px solid #bcd0ee;">Produit</th>
                <th style="padding:6px; border:1px solid #bcd0ee;">Q. Initiale</th>
                <th style="padding:6px; border:1px solid #bcd0ee;">Q. Ajoutée</th>
                <th style="padding:6px; border:1px solid #bcd0ee;">Q. Totale</th>
                <th style="padding:6px; border:1px solid #bcd0ee;">Q. Vendue</th>
                <th style="padding:6px; border:1px solid #bcd0ee;">Q. Restée</th>
                <th style="padding:6px; border:1px solid #bcd0ee;">Prix unitaire</th>
                <th style="padding:6px; border:1px solid #bcd0ee;">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($produits as $produit)
            @php
                $stock = $stocks->firstWhere('produit_id', $produit->id);
                $q_init = $stock->quantite_initiale ?? 0;
                $q_ajout = $stock->quantite_ajoutee ?? 0;
                $q_vendue = $stock->quantite_vendue ?? 0;
                $q_total = $q_init + $q_ajout;
                $q_reste = $stock->quantite_reste ?? ($q_total - $q_vendue);
                $prix = $produit->prix_vente;
                $total = $q_vendue * $prix;
            @endphp
            <tr>
                <td style="padding:6px; border:1px solid #bcd0ee; font-weight:bold;">{{ $produit->nom }}</td>
                <td style="padding:6px; border:1px solid #bcd0ee;">{{ $q_init }}</td>
                <td style="padding:6px; border:1px solid #bcd0ee;">{{ $q_ajout }}</td>
                <td style="padding:6px; border:1px solid #bcd0ee; text-align:center;">{{ $q_total }}</td>
                <td style="padding:6px; border:1px solid #bcd0ee;">{{ $q_vendue }}</td>
                <td style="padding:6px; border:1px solid #bcd0ee;">{{ $q_reste }}</td>
                <td style="padding:6px; border:1px solid #bcd0ee; text-align:right;">{{ number_format($prix, 0, ',', ' ') }} F</td>
                <td style="padding:6px; border:1px solid #bcd0ee; text-align:right; font-weight:bold;">{{ number_format($total, 0, ',', ' ') }} F</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div style="margin-top:16px; text-align:right; font-size:16px; font-weight:bold; color:#2563eb;">
        @php
            $totalVente = 0;
            foreach($produits as $produit) {
                $stock = $stocks->firstWhere('produit_id', $produit->id);
                $q_vendue = $stock->quantite_vendue ?? 0;
                $prix = $produit->prix_vente;
                $totalVente += $q_vendue * $prix;
            }
        @endphp
        Total vente session : {{ number_format($totalVente, 0, ',', ' ') }} F
    </div>
</div>