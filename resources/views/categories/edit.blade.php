<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modifier la catégorie
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
            <form method="POST" action="{{ route('categories.update', [$entreprise->id, $categorie->id]) }}">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Nom de la catégorie</label>
                    <input type="text" name="nom" value="{{ old('nom', $categorie->nom) }}" required
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                    @error('nom')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Compte de vente (classe 7)</label>
                    <select name="compte_vente_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">Choisir un compte de vente</option>
                        @foreach($comptesVente as $compte)
                            <option value="{{ $compte->id }}" @selected(old('compte_vente_id', $categorie->compte_vente_id) == $compte->id)>{{ $compte->numero }} — {{ $compte->nom }}</option>
                        @endforeach
                    </select>
                    @error('compte_vente_id')<div class="mt-1 text-xs text-red-500">{{ $message }}</div>@enderror
                    @if($comptesVente->isEmpty())
                        <p class="mt-1 text-xs text-amber-700">Aucun compte de classe 7. <a class="underline" href="{{ route('comptes.create', ['entreprise_id' => $entreprise->id]) }}" target="_blank">Créer un compte</a>.</p>
                    @endif
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Compte de stock (classe 3)</label>
                    <select name="compte_stock_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">Choisir un compte de stock</option>
                        @foreach($comptesStock as $compte)
                            <option value="{{ $compte->id }}" @selected(old('compte_stock_id', $categorie->compte_stock_id) == $compte->id)>{{ $compte->numero }} — {{ $compte->nom }}</option>
                        @endforeach
                    </select>
                    @error('compte_stock_id')<div class="mt-1 text-xs text-red-500">{{ $message }}</div>@enderror
                    @if($comptesStock->isEmpty())
                        <p class="mt-1 text-xs text-amber-700">Aucun compte de classe 3. <a class="underline" href="{{ route('comptes.create', ['entreprise_id' => $entreprise->id]) }}" target="_blank">Créer un compte</a>.</p>
                    @endif
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Variation de stock (classe 6)</label>
                    <select name="compte_variation_stock_id" required class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">Choisir un compte de variation</option>
                        @foreach($comptesVariationStock as $compte)
                            <option value="{{ $compte->id }}" @selected(old('compte_variation_stock_id', $categorie->compte_variation_stock_id) == $compte->id)>{{ $compte->numero }} — {{ $compte->nom }}</option>
                        @endforeach
                    </select>
                    @error('compte_variation_stock_id')<div class="mt-1 text-xs text-red-500">{{ $message }}</div>@enderror
                    @if($comptesVariationStock->isEmpty())
                        <p class="mt-1 text-xs text-amber-700">Aucun compte de classe 6. <a class="underline" href="{{ route('comptes.create', ['entreprise_id' => $entreprise->id]) }}" target="_blank">Créer un compte</a>.</p>
                    @endif
                </div>
                <div class="flex items-center justify-end">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>