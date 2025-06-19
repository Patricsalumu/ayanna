<x-app-layout>
    <div class="max-w-lg mx-auto mt-10 bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-2xl font-bold text-blue-700 mb-6">Éditer le compte</h1>
        <form method="POST" action="{{ route('comptes.update', $compte) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="entreprise_id" value="{{ $compte->entreprise_id }}">
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Numéro</label>
                <input type="text" name="numero" class="w-full border rounded px-3 py-2" required value="{{ old('numero', $compte->numero) }}">
                @error('numero')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Type</label>
                <select name="type" class="w-full border rounded px-3 py-2" required>
                    <option value="actif" @if(old('type', $compte->type)=='actif') selected @endif>Actif</option>
                    <option value="passif" @if(old('type', $compte->type)=='passif') selected @endif>Passif</option>
                </select>
                @error('type')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Description</label>
                <input type="text" name="description" class="w-full border rounded px-3 py-2" value="{{ old('description', $compte->description) }}">
                @error('description')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Nom du compte</label>
                <input type="text" name="nom" class="w-full border rounded px-3 py-2" required value="{{ old('nom', $compte->nom) }}">
                @error('nom')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('comptes.index') }}" class="px-4 py-2 rounded bg-gray-200 text-blue-700 font-semibold hover:bg-blue-100">Annuler</a>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700">Enregistrer</button>
            </div>
        </form>
    </div>
</x-app-layout>
