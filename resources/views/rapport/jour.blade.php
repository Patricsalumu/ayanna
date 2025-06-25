@extends('layouts.app')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold text-center mb-6">
        RAPPORT JOURNALIER DE {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }},
        COMPTORISTE : {{ Auth::user()->name ?? 'John' }}
    </h1>

    <div class="bg-white rounded-xl shadow p-6">
        <table class="min-w-full border border-gray-400 rounded">
            <tbody>
                {{-- RECAP GENERAL --}}
                <tr>
                    <td class="py-3 px-4 font-semibold border border-gray-300">Recette journalière</td>
                    <td colspan="2" class="py-3 px-4 text-right font-bold text-green-600 border border-gray-300">{{ number_format($recette, 0, ',', ' ') }} F</td>
                </tr>
                <tr>
                    <td class="py-3 px-4 font-semibold border border-gray-300">Total Créances</td>
                    <td colspan="2" class="py-3 px-4 text-right font-bold text-orange-600 border border-gray-300">-{{ number_format($totalCreance, 0, ',', ' ') }} F</td>                 
                </tr>
                {{-- TOTAL DÉPENSES --}}
                <tr>
                    <td class="py-3 px-4 font-semibold border border-gray-300">Total Dépenses</td>
                    <td colspan="2" class="py-3 px-4 text-right font-bold text-red-600 border border-gray-300">-{{ number_format($depenses, 0, ',', ' ') }} F</td>
                </tr>
                 {{-- SOLDE FINAL --}}
                <tr>
                    <td class="py-3 px-4 font-bold text-blue-900 border border-gray-300">Solde</td>
                    <td colspan="2" class="py-3 px-4 text-right font-extrabold text-blue-900 text-lg border border-gray-300">
                        {{ number_format($solde, 0, ',', ' ') }} F
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="py-2 px-4 font-bold text-orange-700 border border-gray-300">Détail des Créances</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-semibold border border-gray-300">Client</td>
                    <td class="py-2 px-4 font-semibold border border-gray-300 text-center">Serveuses</td>
                    <td class="py-2 px-4 font-semibold text-right border border-gray-300">Montant</td>
                </tr>
                @forelse($detailsCreance as $detail)
                    <tr>
                        <td class="py-2 px-4 border border-gray-300">
                            {{ $detail['client'] }}
                        </td>
                        <td class="py-2 px-4 border border-gray-300">
                            <div class="flex flex-wrap">
                                @foreach($detail['serveuses'] as $serv)
                                    <span class="inline-block bg-blue-100 text-blue-800 rounded px-2 py-1 text-xs mr-1 mt-1">
                                        {{ $serv }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-2 px-4 text-right text-orange-600 font-semibold border border-gray-300">
                            {{ number_format($detail['total'], 0, ',', ' ') }} F
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-2 px-4 text-gray-400 italic text-right border border-gray-300">Aucune créance ce jour</td></tr>
                @endforelse


                <tr>
                    <td colspan="2" class="py-2 px-4 font-bold text-red-700 border border-gray-300">Détail des Dépenses</td>
                </tr>
                <tr>
                    <td class="py-2 px-4 font-semibold border border-gray-300">Compte</td>
                    <td class="py-2 px-4 font-semibold border border-gray-300">Libellé</td>
                    <td class="py-2 px-4 font-semibold text-right border border-gray-300">Montant</td>
                </tr>
                @php
                    $depensesList = \App\Models\EntreeSortie::whereDate('created_at', $date)
                        ->where('point_de_vente_id', request()->route('pointDeVenteId'))
                        ->whereHas('compte', function($q) {
                            $q->where('type', 'passif');
                        })->get();
                @endphp
                @forelse($depensesList as $dep)
                    <tr>
                        <td class="py-2 px-4 border border-gray-300">
                            <div class="font-semibold">{{ $dep->compte->nom ?? '' }}</div>
                        </td>
                        <td class="py-2 px-4 border border-gray-300">
                            <div class="text-gray-600 text-sm">{{ $dep->libele ?? $dep->motif ?? '' }}</div>
                        </td>
                        <td class="py-2 px-4 text-right text-red-600 font-semibold border border-gray-300">-{{ number_format($dep->montant, 0, ',', ' ') }} F</td>
                    </tr>
                @empty
                <tr>
                        <td colspan="3" class="py-2 px-4 text-gray-400 italic text-right border border-gray-300">Aucune dépense ce jour</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 flex justify-center gap-4">
        <a href="{{ url()->previous() }}" class="inline-block px-6 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold shadow">Retour</a>
        <a href="{{ route('rapport.export_pdf', ['pointDeVenteId' => request()->route('pointDeVenteId'), 'date' => $date]) }}" target="_blank" class="inline-block px-6 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow">Exporter PDF</a>
    </div>
</div>
@endsection
