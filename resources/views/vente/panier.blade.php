<div class="container mx-auto py-8">

    <h1 class="text-2xl font-bold mb-6">🛒 Panier</h1>

    <!-- Options principales (sélections + paiement + menu ...) sur une seule ligne -->
    <div class="flex flex-row gap-2 mb-2 items-center w-full whitespace-nowrap overflow-x-auto">
        <!-- Sélection client -->
        <div class="flex flex-col justify-center min-w-[120px]">
            <label for="client_id" class="text-xs font-semibold mb-0 leading-tight">Client</label>
            <select id="client_id" name="client_id" class="border rounded px-2 py-1 w-full text-sm h-8">
                <option value="">Client</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->nom }}</option>
                @endforeach
            </select>
        </div>
        <!-- Sélection serveuse -->
        <div class="flex flex-col justify-center min-w-[120px]">
            <label for="serveuse_id" class="text-xs font-semibold mb-0 leading-tight">Serveuse</label>
            <select id="serveuse_id" name="serveuse_id" class="border rounded px-2 py-1 w-full text-sm h-8">
                <option value="">Serveuse</option>
                @foreach($serveuses as $serveuse)
                    <option value="{{ $serveuse->id }}">{{ $serveuse->name }}</option>
                @endforeach
            </select>
        </div>
        <!-- Paiement -->
        <button class="bg-green-600 text-white px-4 py-2 rounded font-bold hover:bg-green-700 flex-shrink-0 h-8">Paiement</button>
        <!-- Menu ... -->
        <div class="relative flex-shrink-0">
            <button id="menu-options" type="button" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-xl font-bold">⋯</button>
            <div id="menu-dropdown" class="hidden absolute right-0 mt-2 w-44 bg-white rounded shadow-lg z-10">
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100">Imprimer addition</button>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100">Annuler</button>
            </div>
        </div>
    </div>



    <script>
    // Affichage du menu ...
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('menu-options');
        const menu = document.getElementById('menu-dropdown');
        if(btn && menu) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });
            document.addEventListener('click', function() {
                menu.classList.add('hidden');
            });
        }
    });
    </script>

    <div class="flex flex-col h-[calc(100vh-180px)] w-full">
        <div class="flex-1">
            @if(count($panier) === 0)
                <div class="text-gray-500 italic text-center py-8">Votre panier est vide.</div>
            @else
                @php
                    $total = 0;
                    $maxVisible = 5;
                    $nbProduits = count($produits);
                @endphp
                <div class="bg-white rounded-2xl shadow p-0 overflow-hidden mb-4 h-full"
                     style="max-height: 270px; min-height: 120px; overflow-y: auto;">
                    <table class="w-full text-base">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Produit</th>
                                <th class="px-2 py-3 text-center font-semibold text-gray-700">Qté</th>
                                <th class="px-2 py-3 text-right font-semibold text-gray-700">PU</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produits as $i => $produit)
                                @if($i < $maxVisible)
                                    @php
                                        $qte = $panier[$produit->id]['quantite'] ?? 0;
                                        $ligne = $qte * $produit->prix_vente;
                                        $total += $ligne;
                                    @endphp
                                    <tr class="border-b hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $produit->nom }}</td>
                                        <td class="px-2 py-3 text-center">{{ $qte }}</td>
                                        <td class="px-2 py-3 text-right">{{ number_format($produit->prix_vente, 0, ',', ' ') }} F</td>
                                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($ligne, 0, ',', ' ') }} F</td>
                                    </tr>
                                @endif
                            @endforeach
                            @if($nbProduits > $maxVisible)
                                <tr class="bg-gray-50 text-center text-gray-500">
                                    <td colspan="4">… et {{ $nbProduits - $maxVisible }} article(s) supplémentaire(s)</td>
                                </tr>
                            @endif
                            <tr class="bg-gray-100 font-bold">
                                <td colspan="3" class="text-right px-4 py-3">Total</td>
                                <td class="px-4 py-3 text-right">{{ number_format($total, 0, ',', ' ') }} F</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end">
                    <a href="{{ route('vente.catalogue', $pointDeVente->id) }}" class="bg-blue-600 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-blue-700 transition">Retour au catalogue</a>
                </div>
            @endif
        </div>
        <!-- Pavé numérique compact, horizontal, toujours visible -->
        <div class="mb-4 w-full">
            <label class="mb-2 font-semibold block text-center">Pavé numérique</label>
            <div class="grid grid-cols-4 gap-2 w-full max-w-full mx-auto"
                 style="min-height:56px;">
                @foreach([7,8,9,'C',4,5,6,0,1,2,3,'Valider'] as $n)
                    @if($n === 'Valider')
                        <button class="bg-yellow-200 hover:bg-yellow-300 text-lg font-bold py-2 rounded-lg w-full col-span-2">Valider</button>
                    @elseif($n === 'C')
                        <button class="bg-red-200 hover:bg-red-300 text-lg font-bold py-2 rounded-lg w-full">C</button>
                    @else
                        <button class="bg-gray-200 hover:bg-gray-300 text-lg font-bold py-2 rounded-lg w-full">{{ $n }}</button>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
