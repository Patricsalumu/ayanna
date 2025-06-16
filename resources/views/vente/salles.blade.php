<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Salles du point de vente : {{ $pointDeVente->nom }}
        </h2>
    </x-slot>
    <div class="p-6">
        @if($salles->isEmpty())
            <div class="text-gray-600 italic">Aucune salle n'est associée à ce point de vente.</div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach($salles as $salle)
                    <a href="{{ route('salle.plan', ['entreprise' => $pointDeVente->entreprise_id, 'salle' => $salle->id]) }}"
                       class="block bg-white shadow rounded-xl p-6 hover:bg-blue-50 transition border border-gray-200">
                        <h3 class="text-lg font-bold mb-2 text-gray-800">{{ $salle->nom }}</h3>
                        <div class="text-gray-500">Tables : {{ $salle->tables->count() }}</div>
                    </a>
                @endforeach
            </div>
        @endif
        <div class="mt-8">
            <a href="{{ route('pointsDeVente.show', [$pointDeVente->entreprise_id, $pointDeVente->id]) }}" class="text-blue-600 hover:underline">&larr; Retour aux points de vente</a>
        </div>
    </div>
</x-app-layout>
