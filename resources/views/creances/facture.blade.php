<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture Créance #{{ $commande->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 18px;
            color: #3B82F6;
            margin-top: 10px;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-box {
            width: 45%;
            padding: 15px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            background-color: #F9FAFB;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #1F2937;
            font-size: 14px;
            font-weight: bold;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #D1D5DB;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        .table th {
            background-color: #F3F4F6;
            font-weight: bold;
            color: #374151;
        }
        .table .text-right {
            text-align: right;
        }
        .table .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #FEF3C7;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 10px;
            color: #6B7280;
        }
        .status-paid {
            color: #059669;
            font-weight: bold;
        }
        .status-pending {
            color: #D97706;
            font-weight: bold;
        }
        .paiements-section {
            margin-top: 30px;
        }
        .no-print {
            margin-top: 30px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            background-color: #3B82F6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #2563EB;
        }
        .btn-secondary {
            background-color: #6B7280;
        }
        .btn-secondary:hover {
            background-color: #4B5563;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="company-name">{{ $commande->panier->pointDeVente->entreprise->nom ?? 'Ayanna' }}</div>
        <div style="font-size: 12px; color: #6B7280;">
            {{ $commande->panier->pointDeVente->nom ?? 'Point de vente' }}
        </div>
        <div class="document-title">FACTURE CRÉANCE</div>
        <div style="font-size: 14px; margin-top: 10px;">
            Facture N° {{ $commande->id }} - {{ \Carbon\Carbon::parse($commande->created_at)->format('d/m/Y') }}
        </div>
    </div>

    <!-- Informations -->
    <div class="info-section">
        <div class="info-box">
            <h3>INFORMATIONS CLIENT</h3>
            <div class="info-row">
                <span>Client :</span>
                <span>{{ $commande->panier->client->nom ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span>Table :</span>
                <span>{{ $commande->panier->tableResto->numero ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span>Serveuse :</span>
                <span>{{ $commande->panier->serveuse->name ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="info-box">
            <h3>INFORMATIONS COMMANDE</h3>
            <div class="info-row">
                <span>Date commande :</span>
                <span>{{ \Carbon\Carbon::parse($commande->created_at)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span>Mode paiement :</span>
                <span>Compte client</span>
            </div>
            <div class="info-row">
                <span>Statut :</span>
                <span class="{{ $commande->statut === 'payé' ? 'status-paid' : 'status-pending' }}">
                    {{ $commande->statut === 'payé' ? 'PAYÉ' : 'EN ATTENTE' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Détail des produits -->
    <table class="table">
        <thead>
            <tr>
                <th>Produit</th>
                <th class="text-center">Quantité</th>
                <th class="text-right">Prix unitaire</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $montantTotal = 0;
            @endphp
            @foreach($commande->panier->produits as $produit)
                @php
                    $totalProduit = $produit->pivot->quantite * $produit->prix_vente;
                    $montantTotal += $totalProduit;
                @endphp
                <tr>
                    <td>{{ $produit->nom }}</td>
                    <td class="text-center">{{ $produit->pivot->quantite }}</td>
                    <td class="text-right">{{ number_format($produit->prix_vente, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($totalProduit, 0, ',', ' ') }} F</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3"><strong>TOTAL FACTURE</strong></td>
                <td class="text-right"><strong>{{ number_format($montantTotal, 0, ',', ' ') }} F</strong></td>
            </tr>
        </tfoot>
    </table>

    @if($commande->paiements->isNotEmpty())
        <!-- Historique des paiements -->
        <div class="paiements-section">
            <h3 style="color: #1F2937; margin-bottom: 15px;">HISTORIQUE DES PAIEMENTS</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-right">Montant</th>
                        <th class="text-center">Mode</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commande->paiements as $paiement)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y H:i') }}</td>
                            <td class="text-right">{{ number_format($paiement->montant, 0, ',', ' ') }} F</td>
                            <td class="text-center">{{ ucfirst($paiement->mode) }}</td>
                            <td>{{ $paiement->notes ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php
                        $montantPaye = $commande->paiements->sum('montant');
                        $montantRestant = $montantTotal - $montantPaye;
                    @endphp
                    <tr style="background-color: #DBEAFE;">
                        <td colspan="3"><strong>TOTAL PAYÉ</strong></td>
                        <td class="text-right"><strong>{{ number_format($montantPaye, 0, ',', ' ') }} F</strong></td>
                    </tr>
                    @if($montantRestant > 0)
                        <tr style="background-color: #FEF3C7;">
                            <td colspan="3"><strong>MONTANT RESTANT DÛ</strong></td>
                            <td class="text-right"><strong style="color: #D97706;">{{ number_format($montantRestant, 0, ',', ' ') }} F</strong></td>
                        </tr>
                    @else
                        <tr style="background-color: #D1FAE5;">
                            <td colspan="4" class="text-center"><strong style="color: #059669;">✓ CRÉANCE ENTIÈREMENT SOLDÉE</strong></td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    @else
        <!-- Résumé financier sans paiements -->
        <div style="background-color: #FEF3C7; padding: 15px; border-radius: 8px; text-align: center; margin-top: 20px;">
            <strong style="color: #D97706;">MONTANT TOTAL DÛ : {{ number_format($montantTotal, 0, ',', ' ') }} F</strong>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Facture générée le {{ now()->format('d/m/Y à H:i') }}</p>
        <p>{{ $commande->panier->pointDeVente->entreprise->nom ?? 'Ayanna' }} - Gestion des créances</p>
    </div>

    <!-- Boutons d'action (non imprimables) -->
    <div class="no-print">
        <button onclick="window.print()" class="btn">Imprimer</button>
        <a href="{{ route('creances.liste') }}" class="btn btn-secondary">Retour à la liste</a>
        <a href="{{ route('creances.historique', $commande->id) }}" class="btn btn-secondary">Voir l'historique</a>
    </div>
</body>
</html>
