<x-app-layout>
    <div class="max-w-lg mx-auto mt-10 bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-2xl font-bold text-blue-700 mb-6">Créer un compte</h1>
        <form method="POST" action="{{ route('comptes.store') }}">
            @csrf
            <input type="hidden" name="entreprise_id" value="{{ $entreprise_id }}">
            
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Classe comptable</label>
                <select name="classe_comptable_id" class="w-full border rounded px-3 py-2" required onchange="updateTypeField(this)">
                    <option value="">-- Sélectionner une classe --</option>
                    @foreach($classesComptables as $classe)
                        <option value="{{ $classe->id }}" 
                                data-numero="{{ $classe->numero }}"
                                @if(old('classe_comptable_id') == $classe->id) selected @endif>
                            Classe {{ $classe->numero }} - {{ $classe->nom }}
                        </option>
                    @endforeach
                </select>
                @error('classe_comptable_id')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-semibold">Numéro</label>
                <input type="text" name="numero" class="w-full border rounded px-3 py-2" required value="{{ old('numero') }}" placeholder="Ex: 512000">
                @error('numero')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
                <div class="text-xs text-gray-500 mt-1">Le numéro doit commencer par le numéro de la classe sélectionnée</div>
            </div>
            
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Type</label>
                <select name="type" id="type" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="actif" @if(old('type')=='actif') selected @endif>Actif</option>
                    <option value="passif" @if(old('type')=='passif') selected @endif>Passif</option>
                    <option value="charge" @if(old('type')=='charge') selected @endif>Charge</option>
                    <option value="produit" @if(old('type')=='produit') selected @endif>Produit</option>
                </select>
                @error('type')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Nom du compte</label>
                <input type="text" name="nom" class="w-full border rounded px-3 py-2" required value="{{ old('nom') }}" placeholder="Ex: Banque">
                @error('nom')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Description</label>
                <input type="text" name="description" class="w-full border rounded px-3 py-2" value="{{ old('description') }}" placeholder="Description optionnelle">
                @error('description')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            
            <div class="flex justify-end gap-2">
                <a href="{{ route('comptes.index') }}" class="px-4 py-2 rounded bg-gray-200 text-blue-700 font-semibold hover:bg-blue-100">Annuler</a>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700">Créer</button>
            </div>
        </form>
    </div>

    <script>
    function updateTypeField(selectElement) {
        const typeSelect = document.getElementById('type');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const classeNumero = selectedOption.getAttribute('data-numero');
        
        // Réinitialiser les options
        typeSelect.value = '';
        
        // Suggérer le type en fonction de la classe
        if (classeNumero) {
            let suggestedType = '';
            switch(classeNumero) {
                case '1':
                case '2':
                case '3':
                    suggestedType = 'actif';
                    break;
                case '4':
                case '5':
                    suggestedType = 'passif';
                    break;
                case '6':
                    suggestedType = 'charge';
                    break;
                case '7':
                    suggestedType = 'produit';
                    break;
            }
            if (suggestedType) {
                typeSelect.value = suggestedType;
            }
        }
    }
    </script>
</x-app-layout>
