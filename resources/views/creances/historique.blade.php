@extends('layouts.appvente')
@section('content')

<div class="max-w-5xl mx-auto px-6 py-6">
    <!-- En-tête -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Historique des paiements</h1>
                <p class="text-gray-600">Créance #{{ $commande->id }}</p>
            </div>
            <a href="{{ route('creances.liste') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour
            </a>
        </div>

        <!-- Informations de la créance -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <h3 class="font-bold text-blue-700 mb-3">Détails de la commande</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Table :</span>
                        <span class="font-medium">{{ $commande->panier->tableResto->numero ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Client :</span>
                        <span class="font-medium">{{ $commande->panier->client->nom ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Serveuse :</span>
                        <span class="font-medium">{{ $commande->panier->serveuse->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Date commande :</span>
                        <span class="font-medium">{{ \Carbon\Carbon::parse($commande->created_at)->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                <h3 class="font-bold text-green-700 mb-3">Résumé financier</h3>
                @php
                    $montantTotal = $commande->montant ?? $commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente);
                    $montantPaye = $commande->paiements->sum('montant');
                    $montantRestant = $montantTotal - $montantPaye;
                @endphp
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Montant total :</span>
                        <span class="font-bold text-green-600">{{ number_format($montantTotal, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Montant payé :</span>
                        <span class="font-medium text-blue-600">{{ number_format($montantPaye, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between border-t border-green-300 pt-2">
                        <span class="text-gray-600 font-bold">Montant restant :</span>
                        <span class="font-bold {{ $montantRestant <= 0 ? 'text-green-600' : 'text-orange-600' }}">
                            {{ number_format($montantRestant, 0, ',', ' ') }} F
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique des paiements -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Historique des paiements</h2>
        </div>

        @if($commande->paiements->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Date</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-gray-700">Montant</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">Mode</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Notes</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">Enregistré par</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($commande->paiements as $paiement)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <!-- Date -->
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($paiement->created_at)->format('H:i') }}</div>
                                </td>
                                
                                <!-- Montant -->
                                <td class="px-6 py-4 text-right">
                                    <div class="font-bold text-green-600 text-lg">
                                        {{ number_format($paiement->montant, 0, ',', ' ') }} F
                                    </div>
                                </td>
                                
                                <!-- Mode -->
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        {{ $paiement->mode === 'especes' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $paiement->mode === 'carte' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $paiement->mode === 'cheque' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $paiement->mode === 'virement' ? 'bg-purple-100 text-purple-700' : '' }}
                                        {{ $paiement->mode === 'mobile' ? 'bg-orange-100 text-orange-700' : '' }}">
                                        {{ ucfirst($paiement->mode) }}
                                    </span>
                                </td>
                                
                                <!-- Notes -->
                                <td class="px-6 py-4">
                                    <div class="text-gray-700 text-sm">{{ $paiement->notes ?: '-' }}</div>
                                </td>
                                
                                <!-- Enregistré par -->
                                <td class="px-6 py-4 text-center">
                                    <div class="text-gray-700">{{ $paiement->user->name ?? 'Système' }}</div>
                                </td>
                                
                                <!-- Statut -->
                                <td class="px-6 py-4 text-center">
                                    @if($paiement->est_solde)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-green-100 text-green-700 font-medium text-sm border border-green-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Soldé
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-orange-100 text-orange-700 font-medium text-sm border border-orange-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Partiel
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-green-50 border-t-2 border-green-200">
                        <tr>
                            <td colspan="5" class="px-6 py-4 font-bold text-green-700">Total payé :</td>
                            <td class="px-6 py-4 text-center font-bold text-green-700 text-lg">
                                {{ number_format($montantPaye, 0, ',', ' ') }} F
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun paiement enregistré</h3>
                <p class="text-gray-500">Cette créance n'a pas encore fait l'objet de paiements.</p>
            </div>
        @endif
    </div>

    <!-- Détail des produits -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 mt-6">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Détail de la commande</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Produit</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">Quantité</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-700">Prix unitaire</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-700">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($commande->panier->produits as $produit)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $produit->nom }}</td>
                            <td class="px-6 py-4 text-center text-gray-700">{{ $produit->pivot->quantite }}</td>
                            <td class="px-6 py-4 text-right text-gray-700">{{ number_format($produit->prix_vente, 0, ',', ' ') }} F</td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">
                                {{ number_format($produit->pivot->quantite * $produit->prix_vente, 0, ',', ' ') }} F
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-green-50 border-t-2 border-green-200">
                    <tr>
                        <td colspan="3" class="px-6 py-4 font-bold text-green-700">Total commande :</td>
                        <td class="px-6 py-4 text-right font-bold text-green-700 text-lg">
                            {{ number_format($montantTotal, 0, ',', ' ') }} F
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
