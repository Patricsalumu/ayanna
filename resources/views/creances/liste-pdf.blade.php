<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Créances - {{ $periode }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 11px; 
            line-height: 1.4; 
            color: #333;
            margin: 20px;
        }
        
        .header { 
            text-align: center; 
            border-bottom: 3px solid #2563eb; 
            padding-bottom: 20px; 
            margin-bottom: 25px; 
            position: relative;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .logo { 
            width: 70px; 
            height: 70px; 
            margin-right: 20px;
            border-radius: 8px;
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
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 25px; 
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
            font-size: 10px; 
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
            background: linear-gradient(135deg, #3b82f6, #1d4ed8); 
            color: white; 
            padding: 8px 6px; 
            text-align: left; 
            font-weight: bold; 
            font-size: 9px;
        }
        
        th.text-center { text-align: center; }
        th.text-right { text-align: right; }
        
        td { 
            padding: 6px; 
            border-bottom: 1px solid #e5e7eb; 
            vertical-align: middle;
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
            @if($entreprise && $entreprise->logo)
                <img src="{{ public_path('storage/logos/' . $entreprise->logo) }}" alt="Logo" class="logo">
            @else
                <img src="{{ public_path('storage/logos/favicon.png') }}" alt="Ayanna" class="logo">
            @endif
            
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
    <div class="info-section">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre de créances</div>
                <div class="info-value nombre">{{ $nombreCreances }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Montant total</div>
                <div class="info-value total">{{ number_format($totalGeneral, 0, ',', ' ') }} F</div>
            </div>
            <div class="info-item">
                <div class="info-label">Montant restant à encaisser</div>
                <div class="info-value restant">{{ number_format($totalRestant, 0, ',', ' ') }} F</div>
            </div>
        </div>
    </div>

    <!-- Tableau des créances -->
    @if($creances->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Table</th>
                    <th>Client</th>
                    <th>Serveuse</th>
                    <th>Date</th>
                    <th class="text-right">Montant Total</th>
                    <th class="text-right">Montant Restant</th>
                    <th class="text-center">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creances as $commande)
                    @php
                        $montantTotal = $commande->panier && $commande->panier->produits ? 
                            $commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente) : 0;
                        $montantPaye = $commande->paiements ? $commande->paiements->sum('montant') : 0;
                        $montantRestant = max(0, $montantTotal - $montantPaye);
                    @endphp
                    <tr>
                        <td>
                            <span class="table-numero">
                                {{ $commande->panier->tableResto->numero ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="font-bold">{{ $commande->panier->client->nom ?? 'N/A' }}</td>
                        <td>{{ $commande->panier->serveuse->name ?? 'N/A' }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($commande->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="montant total">
                            {{ number_format($montantTotal, 0, ',', ' ') }} F
                        </td>
                        <td class="montant {{ $montantRestant <= 0 ? 'solde' : 'restant' }}">
                            @if($montantRestant <= 0)
                                Soldé
                            @else
                                {{ number_format($montantRestant, 0, ',', ' ') }} F
                            @endif
                        </td>
                        <td class="text-center">
                            @if($commande->mode_paiement === 'compte_client' && $commande->statut === 'payé')
                                <span class="statut paye">Payé</span>
                            @else
                                <span class="statut attente">En attente</span>
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
            Généré par Ayanna le {{ $dateGeneration->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>
