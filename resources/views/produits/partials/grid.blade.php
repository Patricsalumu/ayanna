<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @foreach($produits as $produit)
        <div class="bg-white shadow rounded-xl p-4 flex flex-row items-center gap-4 h-full">
            @if($produit->image)
                <img src="{{ asset('storage/' . $produit->image) }}"
                     alt="{{ $produit->nom }}"
                     class="w-32 h-32 object-cover rounded border border-gray-200" />
            @else
                <div class="w-32 h-32 flex items-center justify-center bg-gray-100 rounded border border-gray-200 text-gray-400">
                    <span>Aucune image</span>
                </div>
            @endif
            <div class="flex-1">
                <h3 class="text-lg font-semibold mb-1">{{ $produit->nom }}</h3>
                <p class="text-sm text-gray-600 mb-1">{{ $produit->description }}</p>
                <p class="text-xs text-gray-500 mb-1">{{ $produit->categorie->nom ?? '-' }}</p>
                <p class="text-md font-bold mt-2">{{ number_format($produit->prix_vente, 0, ',', ' ') }} FC</p>
            </div>
        </div>
    @endforeach
</div>