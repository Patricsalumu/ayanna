@extends('layouts.appvente')

@section('content')
<div class="container mx-auto p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Bons de Commande</h1>
            <div class="flex gap-4">
                <form method="GET" action="{{ route('bon-commande.index') }}" class="flex gap-2">
                    <input type="date" name="date" value="{{ $date }}" class="px-4 py-2 border rounded-lg">
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
