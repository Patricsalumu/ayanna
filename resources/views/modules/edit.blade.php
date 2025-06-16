<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modifier le module
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
            @if ($errors->any())
                <div class="mb-4 text-red-600">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ __("Erreur : ") . $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 text-green-600">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('modules.update', $module->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700" for="nom">Nom module</label>
                    <input type="text" name="nom" id="nom" value="{{ old('nom', $module->nom) }}" required class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700" for="description">Zone de texte</label>
                    <textarea name="description" rows="5" cols="50" id="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">{{ old('description', $module->description) }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700" for="icon">Icône du module (image)</label>
                    @if($module->icon)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $module->icon) }}" alt="Icône actuelle" class="w-16 h-16 object-contain rounded-full shadow bg-white border">
                        </div>
                    @endif
                    <input type="file" name="icon" id="icon" accept="image/*" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                    <small class="text-gray-500">Laisser vide pour conserver l'icône actuelle.</small>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700" for="disponible">Module disponible ?</label>
                    <select name="disponible" id="disponible" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        <option value="1" {{ old('disponible', $module->disponible) == 1 ? 'selected' : '' }}>Oui</option>
                        <option value="0" {{ old('disponible', $module->disponible) == 0 ? 'selected' : '' }}>Non</option>
                    </select>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
