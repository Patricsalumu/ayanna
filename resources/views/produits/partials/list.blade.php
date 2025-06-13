<div class="overflow-x-auto mt-6">
    <table class="min-w-full bg-white rounded shadow">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left">Nom</th>
                <th class="px-4 py-2 text-left">Catégorie</th>
                <th class="px-4 py-2 text-left">Prix d'Achat</th>
                <th class="px-4 py-2 text-left">Prix de Vente</th>
                <th class="px-4 py-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produits as $index => $produit)
                <tr class="{{ $index % 2 === 0 ? 'bg-gray-50' : 'bg-white' }}">
                    <td class="px-4 py-2">{{ $produit->nom }}</td>
                    <td class="px-4 py-2">{{ $produit->categorie->nom ?? '-' }}</td>
                    <td class="px-4 py-2">{{ number_format($produit->prix_achat, 0, ',', ' ') }} FC</td>
                    <td class="px-4 py-2">{{ number_format($produit->prix_vente, 0, ',', ' ') }} FC</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('produits.edit', [$produit->categorie->entreprise_id, $produit->id]) }}" class="text-indigo-600 hover:underline">Modifier</a>
                        <form action="{{ route('produits.duplicate', [$produit->categorie->entreprise_id, $produit->id]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-yellow-600 hover:underline">Dupliquer</button>
                        </form>
                        <form action="{{ route('produits.destroy', [$produit->categorie->entreprise_id, $produit->id]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce produit ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline ml-2">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
