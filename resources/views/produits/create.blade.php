<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ajouter un produit
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto mt-10">
        @php
            $categories = \App\Models\Categorie::where('entreprise_id', $entreprise->id)->get();
        @endphp

        <form action="{{ route('produits.store', $entreprise->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="categorie_id" class="block text-sm font-medium text-gray-700">Catégorie</label>
                <select name="categorie_id" id="categorie_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Choisir une catégorie</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('categorie_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" name="nom" id="nom" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-700">Image (optionnelle)</label>
                <input type="file" name="image" id="image" class="mt-1 block w-full">
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            </div>

            <div class="mb-4">
                <label for="prix_achat" class="block text-sm font-medium text-gray-700">Prix d'achat</label>
                <input type="number" step="0.01" name="prix_achat" id="prix_achat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>

            <div class="mb-4">
                <label for="default_price" class="block text-sm font-medium text-gray-700">Prix de vente par défaut</label>
                <input type="number" step="0.01" name="default_price" id="default_price" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ old('default_price', 0) }}" required>
                <p class="text-xs text-gray-500 mt-1">Ce prix sera utilisé pour toutes les salles si vous ne spécifiez pas de prix individuel.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Prix par salle</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($salles as $salle)
                        <div>
                            <label for="salle_prices[{{ $salle->id }}]" class="block text-xs font-medium text-gray-700 mb-1">{{ $salle->nom }}</label>
                            <input type="number" step="0.01" name="salle_prices[{{ $salle->id }}]" id="salle_prices[{{ $salle->id }}]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ old('salle_prices.' . $salle->id) }}">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Ajouter</button>
            </div>
        </form>
    </div>
</x-app-layout>
