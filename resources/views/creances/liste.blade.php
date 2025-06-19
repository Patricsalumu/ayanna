<x-app-layout>
    <div class="max-w-5xl mx-auto mt-8 bg-white rounded-xl shadow-lg p-6 flex gap-6">
        <div class="flex-1">
            <div class="flex justify-between items-center mb-6">
                <a href="{{ url()->previous() }}" class="inline-block px-4 py-2 rounded bg-gray-200 hover:bg-blue-100 text-blue-700 font-semibold shadow transition">&larr; Retour</a>
                <div class="flex-1 flex flex-col items-center">
                    <h1 class="text-2xl font-bold text-blue-700 text-center">Liste des créances (paiement compte client)</h1>
                    <input type="text" id="search-creance" placeholder="Rechercher client, serveuse, heure..." class="mt-2 px-3 py-2 border rounded w-80 focus:ring focus:border-blue-400 text-base shadow-sm" oninput="filtrerCreances()">
                </div>
                <div class="text-right min-w-[180px]">
                    <div class="text-sm text-gray-500">Total créances du jour</div>
                    <div class="text-xl font-bold text-green-700">
                        {{ number_format($creances->filter(fn($commande) => !($commande->mode_paiement === 'compte_client' && $commande->statut === 'payé'))->sum(fn($commande) => $commande->panier && $commande->panier->produits ? $commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente) : 0), 0, ',', ' ') }} F
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="table-creances" class="min-w-full bg-white border rounded-lg">
                    <thead>
                        <tr class="bg-blue-100 text-blue-700">
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Client</th>
                            <th class="px-4 py-2">Serveuse</th>
                            <th class="px-4 py-2">Heure</th>
                            <th class="px-4 py-2">Montant</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody id="body-creances">
                        @foreach($creances as $commande)
                            <tr class="border-b hover:bg-blue-50 transition cursor-pointer" onclick="afficherDetails({{ $commande->id }})" data-client="{{ strtolower($commande->panier->client->nom ?? '') }}" data-serveuse="{{ strtolower($commande->panier->serveuse->name ?? '') }}" data-heure="{{ \Carbon\Carbon::parse($commande->created_at)->format('H:i') }}">
                                <td class="px-4 py-2 text-center font-semibold">{{ $commande->id }}</td>
                                <td class="px-4 py-2 text-center">{{ $commande->panier->client->nom ?? 'N/A' }}</td>
                                <td class="px-4 py-2 text-center">{{ $commande->panier->serveuse->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2 text-center">{{ \Carbon\Carbon::parse($commande->created_at)->format('H:i') }}</td>
                                <td class="px-4 py-2 text-center font-bold text-green-700">{{ number_format($commande->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente), 0, ',', ' ') }} F</td>
                                <td class="px-4 py-2 text-center">
                                    @if($commande->mode_paiement === 'compte_client' && $commande->statut === 'payé')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold text-sm shadow-sm border border-green-300">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Payé
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('creances.confirmer', $commande->id) }}" onsubmit="return confirm('Confirmer le paiement de cette créance ?');">
                                            @csrf
                                            <button type="submit" class="px-4 py-1 rounded bg-green-600 text-white font-bold shadow hover:bg-green-700 transition">Payer</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div id="pagination-creances" class="flex justify-center mt-4 gap-2"></div>
            </div>
        </div>
        <div id="details-creance" class="w-96 hidden border-l pl-6 relative">
            <button onclick="fermerDetails()" class="absolute top-2 right-2 text-gray-400 hover:text-red-600 text-2xl font-bold" title="Fermer">&times;</button>
            <h2 class="text-xl font-bold mb-4 text-blue-700">Détail du panier</h2>
            <div id="contenu-details"></div>
        </div>
    </div>
    <script>
        const creances = @json($creances);
        let page = 1;
        const lignesParPage = 8;

        function afficherDetails(id) {
            const zone = document.getElementById('details-creance');
            const contenu = document.getElementById('contenu-details');
            const commande = creances.find(c => c.id === id);
            if (!commande || !commande.panier || !commande.panier.produits) return;
            let html = `<table class='min-w-full text-sm'><thead><tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Total</th></tr></thead><tbody>`;
            commande.panier.produits.forEach(prod => {
                html += `<tr><td>${prod.nom}</td><td class='text-center'>${prod.pivot.quantite}</td><td class='text-right'>${prod.prix_vente.toLocaleString()} F</td><td class='text-right'>${(prod.pivot.quantite * prod.prix_vente).toLocaleString()} F</td></tr>`;
            });
            html += '</tbody></table>';
            contenu.innerHTML = html;
            zone.classList.remove('hidden');
        }
        function fermerDetails() {
            document.getElementById('details-creance').classList.add('hidden');
        }
        function filtrerCreances() {
            const search = document.getElementById('search-creance').value.toLowerCase();
            const rows = document.querySelectorAll('#body-creances tr');
            rows.forEach(row => {
                const client = row.getAttribute('data-client');
                const serveuse = row.getAttribute('data-serveuse');
                const heure = row.getAttribute('data-heure');
                // On stocke le résultat du filtre dans un attribut personnalisé
                if (client.includes(search) || serveuse.includes(search) || heure.includes(search)) {
                    row.setAttribute('data-visible', '1');
                } else {
                    row.setAttribute('data-visible', '0');
                }
            });
            page = 1;
            paginerCreances();
        }

        function paginerCreances() {
            const rows = Array.from(document.querySelectorAll('#body-creances tr'));
            // On ne pagine que les lignes visibles selon le filtre
            const visibles = rows.filter(row => row.getAttribute('data-visible') !== '0');
            rows.forEach(row => row.style.display = 'none');
            visibles.forEach((row, i) => {
                row.style.display = (i >= (page-1)*lignesParPage && i < page*lignesParPage) ? '' : 'none';
            });
            const nbPages = Math.ceil(visibles.length / lignesParPage);
            const pagDiv = document.getElementById('pagination-creances');
            pagDiv.innerHTML = '';
            for(let i=1; i<=nbPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className = 'px-3 py-1 rounded border mx-1 ' + (i===page ? 'bg-blue-600 text-white' : 'bg-white text-blue-700 hover:bg-blue-100');
                btn.onclick = () => { page = i; paginerCreances(); };
                pagDiv.appendChild(btn);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Initialisation : toutes les lignes sont visibles
            document.querySelectorAll('#body-creances tr').forEach(row => row.setAttribute('data-visible', '1'));
            paginerCreances();
        });
    </script>
</x-app-layout>