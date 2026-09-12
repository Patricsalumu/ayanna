@extends('layouts.appvente')

@section('content')
<div class="container mx-auto py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Module Restaurant / Bar</h1>
            <p class="text-sm text-gray-600">Bienvenue {{ auth()->user()?->name }} — {{ auth()->user()?->role }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rounded bg-gray-800 px-4 py-2 text-white">Déconnexion</button>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach($tables as $table)
            <a href="{{ route('vente.catalogue', ['pointDeVente' => $pointDeVente->id]) }}?table_id={{ $table->id }}"
               class="rounded border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Table {{ $table->numero }}</h2>
                    <span class="rounded-full {{ $table->serveuse_id ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }} px-3 py-1 text-xs font-semibold">
                        {{ $table->serveuse_id ? 'Assignée' : 'Libre' }}
                    </span>
                </div>
                <div class="mt-3 space-y-1 text-sm text-gray-600">
                    <p><span class="font-medium text-gray-700">Salle :</span> {{ $table->salle?->nom ?? 'Inconnue' }}</p>
                    <p><span class="font-medium text-gray-700">Serveuse :</span> {{ $table->serveuse?->name ?? 'Aucune' }}</p>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
