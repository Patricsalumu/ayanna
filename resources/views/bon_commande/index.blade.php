@extends('layouts.appvente')

@section('content')
<div class="container mx-auto p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Bons de commande</h1>
                <p class="text-sm text-gray-500">Suivi rapide des bons envoyés pour la journée.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <form method="GET" action="{{ route('bon-commande.index') }}" class="flex flex-wrap gap-2">
                    <input type="date" name="date" value="{{ $date }}" class="px-4 py-2 border rounded-lg">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Recherche" class="px-4 py-2 border rounded-lg min-w-[180px]">
                    <input type="text" name="client" value="{{ $clientFilter }}" placeholder="Client" class="px-4 py-2 border rounded-lg min-w-[160px]">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Filtrer
                    </button>
                </form>
                <a href="{{ route('bon-commande.index', ['date' => \Carbon\Carbon::now()->toDateString()]) }}" 
                   class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Aujourd'hui
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                <div class="text-sm text-blue-700">Total bons</div>
                <div class="text-2xl font-bold text-blue-900">{{ number_format($bonsCount, 0, ',', ' ') }}</div>
            </div>
            <div class="rounded-2xl border border-green-100 bg-green-50 p-4">
                <div class="text-sm text-green-700">Produits listés</div>
                <div class="text-2xl font-bold text-green-900">{{ number_format($produitsCount, 0, ',', ' ') }}</div>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                <div class="text-sm text-amber-700">Montant total</div>
                <div class="text-2xl font-bold text-amber-900">{{ optional(auth()->user()?->entreprise)->formatAmount($montantTotal ?? 0, true, 2) }}</div>
            </div>
        </div>

        @if($bons->count() > 0)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                N° Bon
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Serveuse
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Client
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Panier ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date & Heure
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Produits
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($bons as $bon)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-lg font-bold text-blue-600">
                                    {{ $bon->numero_bon }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <strong>{{ $bon->serveuse?->name ?? '-' }}</strong>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $bon->client?->nom ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    #{{ $bon->panier_id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $bon->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $produits = is_string($bon->produits_json)
                                            ? json_decode($bon->produits_json, true)
                                            : ($bon->produits_json ?? []);
                                    @endphp

                                    @if(is_array($produits) && count($produits) > 0)
                                        <ul class="list-disc list-inside">
                                            @foreach($produits as $produit)
                                                <li>{{ $produit['nom'] }} x{{ $produit['quantite'] }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('bon-commande.print', $bon->id) }}"
                                       class="text-blue-600 hover:text-blue-900 mr-3">
                                        Imprimer
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $bons->render() }}
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-6 py-4 rounded-lg">
                <p class="font-semibold">Aucun bon de commande pour cette date.</p>
            </div>
        @endif
    </div>
</div>
@endsection
