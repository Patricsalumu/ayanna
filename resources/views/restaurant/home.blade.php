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
                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">{{ $table->serveuse_id ? 'Assignée' : 'Libre' }}</span>
                </div>
                <p class="mt-2 text-sm text-gray-600">Salle {{ $table->salle_id }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
