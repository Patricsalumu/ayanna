@extends('layouts.app')
@section('content')
    <div class="container mx-auto">
    @php
        $pointDeVenteId = request('point_de_vente_id');
        $pointDeVente = isset($pointDeVente) ? $pointDeVente : ($pointDeVenteId ? \App\Models\PointDeVente::find($pointDeVenteId) : null);
    @endphp
    <h1 class="text-2xl font-bold mb-4 flex items-center gap-4">
    <a href="{{ $pointDeVente ? route('pointsDeVente.show', [$entreprise->id, $pointDeVente->id]) : '#' }}" class="text-blue-600 hover:underline">&larr;</a>    
    Salle
    <a href="{{ route('paniers.jour') }}" class="ml-auto px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition">Paniers du jour</a>
    </h1>
    <!-- Onglets de navigation entre salles -->
    <div class="flex gap-2 mb-4">
        @foreach($salles as $zone)
            <a href="{{ route('salle.plan.vente', ['entreprise' => $entreprise->id, 'salle' => $zone->id, 'point_de_vente_id' => request('point_de_vente_id')]) }}"
               class="px-4 py-2 rounded font-semibold shadow {{ $zone->id === $salle->id ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-700 hover:bg-green-100' }}">
                {{ $zone->nom }}
            </a>
        @endforeach
    </div>
    <!-- Zone du plan (affichage tables, pas d'édition) -->
    <div id="plan" class="relative w-full h-[500px] border border-gray-300 rounded bg-gray-100 overflow-hidden" style="background-image: linear-gradient(0deg, transparent 24%, #e5e7eb 25%, #e5e7eb 26%, transparent 27%, transparent 74%, #e5e7eb 75%, #e5e7eb 76%, transparent 77%, transparent), linear-gradient(90deg, transparent 24%, #e5e7eb 25%, #e5e7eb 26%, transparent 27%, transparent 74%, #e5e7eb 75%, #e5e7eb 76%, transparent 77%, transparent); background-size: 40px 40px;">
        @foreach ($salle->tables as $table)
            @php
                $tableOccupee = \App\Models\Panier::where('table_id', $table->id)
                    ->where('status', 'en_cours')
                    ->exists();
            @endphp
            <a href="{{ route('vente.catalogue', ['pointDeVente' => request('point_de_vente_id')]) }}?table_id={{ $table->id }}"
               class="table-item absolute border-4 flex items-center justify-center shadow-lg"
               style="
                    top :{{ $table->position_y }}px;
                    left:{{ $table->position_x }}px;
                    width: {{ $table->width ?? 70 }}px;
                    height: {{ $table->height ?? 70 }}px;
                    @if ($table->forme === 'cercle') border-radius: 50%; @endif
                    background: {{ $tableOccupee ? '#4ade80' : '#f3f4f6' }};
                    border-color: #22c55e;
               "
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
@endsection
