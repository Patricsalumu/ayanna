@extends('layouts.app')
@section('content')
<div class="container mx-auto max-w-3xl py-8">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <span>Fiche d'ouverture du stock - {{ $pointDeVente->nom }}</span>
        <span class="ml-auto text-gray-500 text-base">
            @if($lastSession)
                <div class="bg-blue-100 text-blue-800 rounded p-4 mb-6 font-semibold">
                    Dernière session : {{ $lastSession }}
                </div>
            @endif
        </span>
    </h1>
    @if($verrouille)
        <div class="bg-green-100 text-green-800 rounded p-4 mb-6 font-semibold">
            La fiche d'ouverture a déjà été validée. Modification impossible.
        </div>
    @endif
    <form method="POST" action="{{ route('stock_journalier.valider_ouverture') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="point_de_vente_id" value="{{ $pointDeVente->id }}">
        <table class="min-w-full bg-white rounded shadow mb-6">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="p-2 text-left">Produit</th>
                    <th class="p-2 text-center">Qté restante dernière session</th>
                    <th class="p-2 text-center">Qté initiale (à saisir)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produits as $produit)
                <tr class="border-b">
                    <td class="p-2">{{ $produit->nom }}</td>
                    <td class="p-2 text-center">{{ $stocksDerniereSession[$produit->id] ?? 0 }}</td>
                    <td class="p-2 text-center">
                        <input type="number" name="quantite_initiale[{{ $produit->id }}]" min="0" step="1" class="border rounded px-2 py-1 w-24 text-center" value="{{ $stocksDerniereSession[$produit->id] ?? 0 }}" @if($verrouille) disabled @endif>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(!$verrouille)
        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-semibold shadow hover:bg-blue-700 transition">Valider</button>
            <a href="{{ route('pointsDeVente.show', [$pointDeVente->entreprise_id, $pointDeVente->id]) }}" class="bg-gray-300 text-gray-800 px-6 py-2 rounded font-semibold shadow hover:bg-gray-400 transition">Annuler</a>
        </div>
        @endif
    </form>
</div>
@endsection
