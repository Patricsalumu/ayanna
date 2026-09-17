@extends('layouts.appsalle')

@section('title', 'Entreprises')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Gestion des entreprises</h1>
        <p class="mt-1 text-sm text-gray-600">Accès réservé au super administrateur.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Entreprise</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Contact</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Utilisateurs</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Blocked</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($entreprises as $entreprise)
                        <tr class="{{ $entreprise->blocked ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $entreprise->nom }}</div>
                                <div class="text-xs text-gray-500">ID #{{ $entreprise->id }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $entreprise->telephone ?: ($entreprise->email ?: '-') }}</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700">{{ $entreprise->users_count }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $entreprise->blocked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $entreprise->blocked ? '1' : '0' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ route('super-admin.entreprises.blocked', $entreprise) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="blocked" value="{{ $entreprise->blocked ? '0' : '1' }}">
                                    <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-white {{ $entreprise->blocked ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                                        {{ $entreprise->blocked ? 'Débloquer' : 'Bloquer' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Aucune entreprise trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <strong>Attention :</strong> Toute utilisation d'un logiciel sans autorisation de l'éditeur ou toute autre forme de piratage expose votre établissement à des poursuites judiciaires.
    </div>
</div>
@endsection
