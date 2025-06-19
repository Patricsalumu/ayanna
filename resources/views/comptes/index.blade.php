<x-app-layout>
    <div class="max-w-3xl mx-auto mt-8 bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('pointsDeVente.show', $entreprise->id) }}" class="px-4 py-2 rounded bg-gray-200 hover:bg-blue-100 text-blue-700 font-semibold shadow transition">&larr; Retour</a>
            <h1 class="text-2xl font-bold text-blue-700">Comptes</h1>
            <a href="{{ route('comptes.create') }}" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition">+ Nouveau compte</a>
        </div>
        <div class="flex justify-end mb-4">
            <input type="text" id="search-compte" placeholder="Rechercher par nom, numéro, type..." class="px-3 py-2 border rounded w-80 focus:ring focus:border-blue-400 text-base shadow-sm">
        </div>
        @if(session('success'))
            <div class="mb-4 text-green-700 bg-green-100 rounded px-4 py-2">{{ session('success') }}</div>
        @endif
        <table id="table-comptes" class="min-w-full bg-white border rounded-lg">
            <thead>
                <tr class="bg-blue-100 text-blue-700">
                    <th class="px-4 py-2">Numéro</th>
                    <th class="px-4 py-2">Nom</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody id="body-comptes">
                @forelse($comptes as $compte)
                    <tr class="border-b hover:bg-blue-50 transition">
                        <td class="px-4 py-2 font-semibold">{{ $compte->numero }}</td>
                        <td class="px-4 py-2 font-semibold">{{ $compte->nom }}</td>
                        <td class="px-4 py-2">{{ ucfirst($compte->type) }}</td>
                        <td class="px-4 py-2">{{ $compte->description }}</td>
                        <td class="px-4 py-2 flex gap-2">
                            <a href="{{ route('comptes.mouvements', $compte) }}" class="px-3 py-1 rounded bg-gray-200 text-blue-700 font-semibold hover:bg-blue-100">Mouvements</a>
                            <a href="{{ route('comptes.edit', $compte) }}" class="px-3 py-1 rounded bg-yellow-400 text-white font-semibold hover:bg-yellow-500">Éditer</a>
                            <form action="{{ route('comptes.destroy', $compte) }}" method="POST" onsubmit="return confirm('Supprimer ce compte ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 rounded bg-red-600 text-white font-semibold hover:bg-red-700">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-8">Aucun compte.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div id="pagination-comptes" class="flex justify-center mt-4 gap-2"></div>
    </div>
    <script>
        let page = 1;
        const lignesParPage = 8;
        function filtrerComptes() {
            const search = document.getElementById('search-compte').value.toLowerCase();
            const rows = document.querySelectorAll('#body-comptes tr');
            rows.forEach(row => {
                const tds = row.querySelectorAll('td');
                if (!tds.length) return;
                const txt = Array.from(tds).slice(0, 4).map(td => td.textContent.toLowerCase()).join(' ');
                if (txt.includes(search)) {
                    row.setAttribute('data-visible', '1');
                } else {
                    row.setAttribute('data-visible', '0');
                }
            });
            page = 1;
            paginerComptes();
        }
        function paginerComptes() {
            const rows = Array.from(document.querySelectorAll('#body-comptes tr'));
            const visibles = rows.filter(row => row.getAttribute('data-visible') !== '0');
            rows.forEach(row => row.style.display = 'none');
            visibles.forEach((row, i) => {
                row.style.display = (i >= (page-1)*lignesParPage && i < page*lignesParPage) ? '' : 'none';
            });
            const nbPages = Math.ceil(visibles.length / lignesParPage);
            const pagDiv = document.getElementById('pagination-comptes');
            pagDiv.innerHTML = '';
            for(let i=1; i<=nbPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className = 'px-3 py-1 rounded border mx-1 ' + (i===page ? 'bg-blue-600 text-white' : 'bg-white text-blue-700 hover:bg-blue-100');
                btn.onclick = () => { page = i; paginerComptes(); };
                pagDiv.appendChild(btn);
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('#body-comptes tr').forEach(row => row.setAttribute('data-visible', '1'));
            paginerComptes();
            document.getElementById('search-compte').addEventListener('input', filtrerComptes);
        });
    </script>
</x-app-layout>
