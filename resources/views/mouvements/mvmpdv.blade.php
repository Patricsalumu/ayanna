<x-app-layout>
    <div class="max-w-2xl mx-auto mt-8 bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-blue-700">Entrées/Sorties du jour - {{ $pointDeVente->nom }}</h1>
            <a href="{{ route('vente.catalogue', ['pointDeVente' => $pointDeVente->id]) }}" class="px-4 py-2 rounded bg-gray-200 text-blue-700 font-semibold hover:bg-blue-100">Retour au catalogue</a>
        </div>
        @if(session('success'))
            <div class="mb-4 text-green-700 bg-green-100 rounded px-4 py-2">{{ session('success') }}</div>
        @endif
        <div class="flex justify-between items-center mb-4 gap-4">
            <div class="text-lg font-bold text-green-700">Total entrée : {{ number_format($totalEntree, 0, ',', ' ') }} F</div>
            <button onclick="document.getElementById('modal-mouvement').classList.remove('hidden')" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700">+ Ajouter un mouvement</button>
            <div class="text-lg font-bold text-red-600">Total sortie : {{ number_format($totalSortie, 0, ',', ' ') }} F</div>
        </div>
        <!-- Modal -->
        <div id="modal-mouvement" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg relative">
                <button onclick="document.getElementById('modal-mouvement').classList.add('hidden')" class="absolute top-2 right-2 text-gray-400 hover:text-red-600 text-2xl font-bold">&times;</button>
                <h2 class="text-xl font-bold mb-4 text-blue-700">Ajouter un mouvement</h2>
                <form method="POST" action="{{ route('mouvements.pdv.store', $pointDeVente->id) }}" class="flex flex-col gap-3">
                    @csrf
                    <div>
                        <label class="block mb-1 font-semibold">Compte</label>
                        <select name="compte_id" class="border rounded px-3 py-2 w-full" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($comptes as $compte)
                                <option value="{{ $compte->id }}">{{ $compte->nom }} ({{ $compte->numero }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold">Montant</label>
                        <input type="number" step="0.01" name="montant" class="border rounded px-3 py-2 w-full" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold">Libellé</label>
                        <input type="text" name="libele" class="border rounded px-3 py-2 w-full" required>
                    </div>
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" onclick="document.getElementById('modal-mouvement').classList.add('hidden')" class="px-4 py-2 rounded bg-gray-200 text-blue-700 font-semibold hover:bg-blue-100">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
        <table class="min-w-full bg-white border rounded-lg">
            <thead>
                <tr class="bg-blue-100 text-blue-700">
                    <th class="px-4 py-2">Compte</th>
                    <th class="px-4 py-2">Libellé</th>
                    <th class="px-4 py-2">Montant</th>
                    <th class="px-4 py-2">Heure</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mouvements as $mvt)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $mvt->compte->nom ?? '' }}</td>
                        <td class="px-4 py-2">{{ $mvt->libele }}</td>
                        <td class="px-4 py-2 text-right font-bold">{{ number_format($mvt->montant, 2, ',', ' ') }} F</td>
                        <td class="px-4 py-2 text-center">{{ $mvt->created_at->format('H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-gray-400 py-8">Aucun mouvement aujourd'hui.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
