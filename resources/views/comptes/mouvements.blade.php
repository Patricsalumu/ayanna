<x-app-layout>
    <div class="max-w-2xl mx-auto mt-8 bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-blue-700">Mouvements du compte {{ $compte->numero }}</h1>
            <a href="{{ route('comptes.index') }}" class="px-4 py-2 rounded bg-gray-200 text-blue-700 font-semibold hover:bg-blue-100">Retour</a>
        </div>
        @if(session('success'))
            <div class="mb-4 text-green-700 bg-green-100 rounded px-4 py-2">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('comptes.mouvements.ajouter', $compte) }}" class="mb-6 flex gap-2 items-end">
            @csrf
            <div>
                <label class="block mb-1 font-semibold">Montant</label>
                <input type="number" step="0.01" name="montant" class="border rounded px-3 py-2 w-32" required>
            </div>
            <div>
                <label class="block mb-1 font-semibold">Libellé</label>
                <input type="text" name="libele" class="border rounded px-3 py-2 w-64" required>
            </div>
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700">Ajouter</button>
        </form>
        <table class="min-w-full bg-white border rounded-lg">
            <thead>
                <tr class="bg-blue-100 text-blue-700">
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Montant</th>
                    <th class="px-4 py-2">Libellé</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mouvements as $mvt)
                    <tr class="border-b">
                        <td class="px-4 py-2 text-center">{{ $mvt->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 text-right font-bold">{{ number_format($mvt->montant, 2, ',', ' ') }} F</td>
                        <td class="px-4 py-2">{{ $mvt->libele }}</td>
                        <td class="px-4 py-2">
                            <form action="{{ route('comptes.mouvements.supprimer', $mvt) }}" method="POST" onsubmit="return confirm('Supprimer ce mouvement ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 rounded bg-red-600 text-white font-semibold hover:bg-red-700">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-gray-400 py-8">Aucun mouvement.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
