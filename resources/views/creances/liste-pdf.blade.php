<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Créances - {{ $periode }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .summary {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .summary td {
            width: 33.33%;
            padding: 9px 12px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            text-align: center;
        }
        .summary-label {
            display: block;
            margin-bottom: 3px;
            color: #6b7280;
            font-size: 9px;
            font-weight: 600;
        }
        .summary-value { font-size: 14px; font-weight: bold; }
        .summary-value.total { color: #059669; }
        .summary-value.restant { color: #dc2626; }
        .summary-value.nombre { color: #2563eb; }
        }
        
        .company-info { 
            text-align: left;
        }
        
        .company-name { 
            font-size: 22px; 
            font-weight: bold; 
            color: #1e40af; 
            margin-bottom: 8px; 
        }
        
        .company-details { 
            font-size: 11px; 
            color: #666; 
            line-height: 1.4;
        }
        
        .document-title { 
            font-size: 18px; 
            font-weight: bold; 
            color: #1f2937; 
            margin-bottom: 8px; 
        }
        
        .document-subtitle { 
            font-size: 13px; 
            color: #6b7280; 
            margin-bottom: 5px;
        }
        
        .generation-info {
            position: absolute;
            top: 0;
            right: 0;
            text-align: right;
            font-size: 9px;
            color: #9ca3af;
        }
        
        .info-section { 
            background-color: #f8fafc; 
            padding: 10px;
            border-radius: 8px; 
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
        }
        
        .info-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr 1fr; 
            gap: 20px; 
        }
        
        .info-item { 
            text-align: center; 
            padding: 10px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        
        .info-label { 
            font-size: 9px;
            color: #6b7280; 
            margin-bottom: 4px; 
            font-weight: 600;
        }
        
        .info-value { 
            font-size: 14px; 
            font-weight: bold; 
        }
        
        .info-value.total { color: #059669; }
        .info-value.restant { color: #dc2626; }
        .info-value.nombre { color: #2563eb; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 25px; 
            font-size: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th { 
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
            padding: 7px 5px;
            text-align: left; 
            font-weight: bold; 
            font-size: 8px;
        }
        
        th.text-center { text-align: center; }
        th.text-right { text-align: right; }
        
        td { 
            padding: 5px;
            border-bottom: 1px solid #e5e7eb; 
            vertical-align: middle;
            overflow-wrap: anywhere;
        }

        tr { page-break-inside: avoid; }
        .products {
            font-size: 7px;
            line-height: 1.35;
            white-space: normal;
            overflow-wrap: anywhere;
            word-wrap: break-word;
        }
        
        tr:nth-child(even) { 
            background-color: #f9fafb; 
        }
        
        .table-numero { 
            background-color: #dbeafe; 
            color: #1e40af; 
            padding: 3px 6px; 
            border-radius: 12px; 
            font-weight: bold; 
            text-align: center; 
            display: inline-block; 
            min-width: 25px; 
            font-size: 9px;
        }
        
        .statut { 
            padding: 2px 6px; 
            border-radius: 10px; 
            font-size: 8px; 
            font-weight: bold; 
            text-align: center;
        }
        
        .statut.paye { 
            background-color: #dcfce7; 
            color: #166534; 
        }
        
        .statut.attente { 
            background-color: #fef3c7; 
            color: #92400e; 
        }
        
        .montant { 
            text-align: right; 
            font-weight: bold; 
        }
        
        .montant.total { color: #059669; }
        .montant.restant { color: #dc2626; }
        .montant.solde { color: #059669; }
        
        .footer { 
            margin-top: 30px; 
            padding-top: 15px; 
            border-top: 1px solid #e5e7eb; 
            font-size: 9px; 
            color: #6b7280; 
            text-align: center; 
        }
        
        .generation-info { 
            font-style: italic; 
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <!-- En-tête professionnel -->
    <div class="header">
        
        <div class="header-content">        
            <div class="company-info">
                <div class="company-name">{{ $entreprise->nom ?? 'Mon Entreprise' }}</div>
                <div class="company-details">
                    @if($entreprise)
                        @if($entreprise->adresse){{ $entreprise->adresse }}<br>@endif
                        @if($entreprise->telephone)Tél: {{ $entreprise->telephone }}@endif
                        @if($entreprise->telephone && $entreprise->email) | @endif
                        @if($entreprise->email)Email: {{ $entreprise->email }}@endif
                    @endif
                </div>
            </div>
        </div>
        
        <div class="document-title">Liste des Créances</div>
        <div class="document-subtitle">
            Période : {{ $periode }}{{ $critereRecherche }}
        </div>
    </div>

    <!-- Résumé des informations -->
    <table class="summary">
        <tr>
            <td>
                <span class="summary-label">Nombre de créances</span>
                <span class="summary-value nombre">{{ $nombreCreances }}</span>
            </td>
            <td>
                <span class="summary-label">Montant total</span>
                <span class="summary-value total">{{ $entreprise?->formatAmount($totalGeneral) ?? number_format($totalGeneral, 0, ',', ' ') }}</span>
            </td>
            <td>
                <span class="summary-label">Montant restant à encaisser</span>
                <span class="summary-value restant">{{ $entreprise?->formatAmount($totalRestant) ?? number_format($totalRestant, 0, ',', ' ') }}</span>
            </td>
        </tr>
    </table>

    <!-- Tableau des créances -->
    @if($creances->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th style="width: 6%;">N° facture</th>
                    <th style="width: 11%;">Date et heure</th>
                    <th style="width: 10%;">Caissier</th>
                    <th style="width: 10%;">Serveuse</th>
                    <th style="width: 10%;">Client</th>
                    <th class="text-right" style="width: 9%;">Total montant</th>
                    <th class="text-right" style="width: 9%;">Restant</th>
                    <th style="width: 35%;">Produits × quantité</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creances as $commande)
                    @php
                        $montantTotal = $commande->panier && $commande->panier->produits ? 
                            $commande->panier->produits->sum(fn($p) => $p->pivot->quantite * (($p->pivot->prix ?? $p->prix_vente) ?? 0)) : 0;
                        $montantPaye = $commande->paiements ? $commande->paiements->sum('montant') : 0;
                        $montantRestant = max(0, $montantTotal - $montantPaye);
                    @endphp
                    <tr>
                        <td class="font-bold">{{ $commande->numero_facture ?? $commande->panier->numero_facture ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($commande->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $commande->panier->openedBy?->name ?? '—' }}</td>
                        <td>{{ $commande->panier->serveuse->name ?? 'N/A' }}</td>
                        <td class="font-bold">{{ $commande->panier->client->nom ?? 'N/A' }}</td>
                        <td class="montant total">
                            {{ $entreprise?->formatAmount($montantTotal) ?? number_format($montantTotal, 0, ',', ' ') }}
                        </td>
                        <td class="montant {{ $montantRestant <= 0 ? 'solde' : 'restant' }}">
                            @if($montantRestant <= 0)
                                Soldé
                            @else
                                {{ $entreprise?->formatAmount($montantRestant) ?? number_format($montantRestant, 0, ',', ' ') }}
                            @endif
                        </td>
                        <td class="products">
                            @if($commande->panier->produits->isEmpty())
                                —
                            @else
                                @foreach($commande->panier->produits as $produit)
                                    @if(!$loop->first), @endif{{ $produit->nom }} x {{ $produit->pivot->quantite }}
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <p>Aucune créance trouvée pour les critères sélectionnés.</p>
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <div class="generation-info">
            Informatisé par Ayanna Erp, Généré le {{ $dateGeneration->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>
