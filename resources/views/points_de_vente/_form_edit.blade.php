<form method="POST" action="{{ route('pointsDeVente.update', [$entreprise->id, $pointDeVente->id]) }}">
    @csrf
    @method('PUT')
    <div class="mb-4">
        <label class="block font-medium text-sm text-gray-700">Nom du point de vente</label>
        <input type="text" name="nom" value="{{ old('nom', $pointDeVente->nom) }}" required class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
    </div>
    <!-- Catégories associées -->
    <div class="mb-4">
        <label class="block font-medium text-sm text-gray-700 mb-2">Catégories associées</label>
        <div class="flex flex-wrap gap-2">
            @foreach($categories as $categorie)
                <label class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full">
                    <input type="checkbox" name="categories[]" value="{{ $categorie->id }}"
                        {{ in_array($categorie->id, old('categories', $categoriesAssociees)) ? 'checked' : '' }}>
                    <span class="ml-2">{{ $categorie->nom }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <!-- Salles associées -->
    <div class="mb-4">
        <label class="block font-medium text-sm text-gray-700 mb-2">Salles associées</label>
        <div class="flex flex-wrap gap-2">
            @foreach($salles as $salle)
                <label class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full">
                    <input type="checkbox" name="salles[]" value="{{ $salle->id }}"
                        {{ in_array($salle->id, old('salles', $sallesAssociees)) ? 'checked' : '' }}>
                    <span class="ml-2">{{ $salle->nom }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <div class="flex items-center justify-end">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Enregistrer</button>
    </div>
</form>
