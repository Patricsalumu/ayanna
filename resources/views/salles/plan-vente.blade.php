@extends('layouts.appvente')
@section('content')
    <div class="container mx-auto">
    @php
        $pointDeVenteId = request('point_de_vente_id');
        $pointDeVente = isset($pointDeVente) ? $pointDeVente : ($pointDeVenteId ? \App\Models\PointDeVente::find($pointDeVenteId) : null);
    @endphp
    <!-- Onglets de navigation entre salles --><br>
    <div class="flex gap-2 mb-4">
        @foreach($salles as $zone)
            <a href="{{ route('salle.plan.vente', ['entreprise' => $entreprise->id, 'salle' => $zone->id, 'point_de_vente_id' => request('point_de_vente_id')]) }}"
               class="px-4 py-2 rounded font-semibold shadow {{ $zone->id === $salle->id ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-700 hover:bg-green-100' }}">
                {{ $zone->nom }}
            </a>
        @endforeach
    </div>
    <!-- Zone du plan (affichage tables, pas d'édition) -->
    <div id="plan" class="relative w-full min-w-[400px] h-[500px] border border-gray-300 rounded bg-gray-100 overflow-x-auto overflow-y-hidden" style="max-width:100vw;">
        <div class="relative w-max h-full">
        @foreach ($salle->tables as $table)
            @php
                $tableOccupee = \App\Models\Panier::where('table_id', $table->id)
                    ->where('status', 'en_cours')
                    ->exists();
                $style = 'top:' . $table->position_y . 'px;'
                    . 'left:' . $table->position_x . 'px;'
                    . 'width:' . ($table->width ?? 70) . 'px;'
                    . 'height:' . ($table->height ?? 70) . 'px;'
                    . ($table->forme === 'cercle' ? 'border-radius:50%;' : '')
                    . 'background:' . ($tableOccupee ? '#4ade80' : '#f3f4f6') . ';'
                    . 'border-color:#22c55e;';
            @endphp
            <a href="{{ route('vente.catalogue', ['pointDeVente' => request('point_de_vente_id')]) }}?table_id={{ $table->id }}"
               class="table-item absolute border-4 flex items-center justify-center shadow-lg"
               style="{{ $style }}"
            >
                <span class="table-num text-center w-full select-none flex items-center justify-center" style="pointer-events:none; font-size:1.3rem; font-weight:bold; color:#222;">{{ $table->numero }}</span>
                @if(isset($table->montant_total) && $table->montant_total > 0)
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full px-2 py-0.5 shadow">
                        {{ number_format($table->montant_total, 0, ',', ' ') }} F
                    </span>
                @endif
            </a>
        @endforeach
        </div>
    </div>
    </div>
@endsection
