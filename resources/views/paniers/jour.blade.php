<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-4">
            <a href="{{ url()->previous() }}" class="text-blue-600 hover:underline">&larr;</a>
            Paniers du jour
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
            @if(session('success'))
                <div class="mb-4 text-green-600 font-bold text-center">{{ session('success') }}</div>
            @endif
            <div class="mb-4 flex items-center justify-between">
                <input type="text" id="search" placeholder="Rechercher client ou serveuse..." class="border rounded px-3 py-2 w-full max-w-md" oninput="filterPaniers()">
            </div>
            <table class="w-full table-auto border" id="paniers-table">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">Table</th>
                        <th class="p-2 text-left">Serveuse</th>
                        <th class="p-2 text-left">Client</th>
                        <th class="p-2 text-left">Ouvert à</th>
                        <th class="p-2 text-left">Statut</th>
                        <th class="p-2 text-left">Montant</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paniers as $panier)
                    <tr class="hover:bg-blue-50 {{ $panier->status === 'en_cours' ? 'cursor-pointer' : 'opacity-60' }}"
                        @if($panier->status === 'en_cours')
                            onclick="window.location='{{ route('vente.catalogue', ['pointDeVente' => $panier->point_de_vente_id]) }}?table_id={{ $panier->table_id }}'"
                        @endif
                        data-produits="{{ strtolower(collect($panier->produits)->pluck('nom')->implode(',')) }}">
                        <td>{{ $panier->tableResto->nom ?? $panier->table_id }}</td>
                        <td>{{ $panier->serveuse->name ?? '-' }}</td>
                        <td>{{ $panier->client->nom ?? '-' }}</td>
                        <td>{{ $panier->created_at->format('H:i') }}</td>
                        <td>{{ $panier->status }}</td>
                        <td>{{ number_format($panier->produits->sum(function($p){ return max(0, $p->pivot->quantite) * $p->prix_vente; }), 0, ',', ' ') }} F</td>
                        <td>
                            @if($panier->status === 'en_cours')
                            <form method="POST" action="{{ route('paniers.annuler', $panier->id) }}" onsubmit="event.stopPropagation(); return confirm('Annuler ce panier ?');" style="display:inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="from" value="jour">
                                <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Annuler</button>
                            </form>
                            @else
                            <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function filterPaniers() {
            const input = document.getElementById('search');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('paniers-table');
            const trs = table.getElementsByTagName('tr');
            for (let i = 1; i < trs.length; i++) {
                const tds = trs[i].getElementsByTagName('td');
                const produits = trs[i].getAttribute('data-produits') || '';
                if (tds.length > 0) {
                    const client = tds[2].textContent.toLowerCase();
                    const serveuse = tds[1].textContent.toLowerCase();
                    if (client.includes(filter) || serveuse.includes(filter) || produits.includes(filter)) {
                        trs[i].style.display = '';
                    } else {
                        trs[i].style.display = 'none';
                    }
                }
            }
        }
    </script>
</x-app-layout>
