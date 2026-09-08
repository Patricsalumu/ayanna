<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Moyens de paiement</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 space-y-6">
            @if(session('success'))
                <div class="p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('modes_paiement.update', $entreprise) }}" class="bg-white shadow rounded p-6">
                @csrf
                @method('PUT')
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b text-left"><th class="py-3">Nom</th><th class="py-3">Code</th><th class="py-3 text-center">Actif</th><th class="py-3">Ordre</th></tr></thead>
                        <tbody>
                        @foreach($modesPaiement as $mode)
                            <tr class="border-b">
                                <td class="py-3 pr-3"><input name="modes[{{ $mode->id }}][nom]" value="{{ $mode->nom }}" required class="w-full rounded border-gray-300"></td>
                                <td class="py-3 pr-3 text-gray-500">{{ $mode->code }}</td>
                                <td class="py-3 text-center">
                                    <input type="hidden" name="modes[{{ $mode->id }}][actif]" value="0">
                                    <input type="checkbox" name="modes[{{ $mode->id }}][actif]" value="1" @checked($mode->actif) @disabled($mode->est_systeme) class="rounded border-gray-300">
                                    @if($mode->est_systeme)<span class="block text-xs text-gray-500">obligatoire</span>@endif
                                </td>
                                <td class="py-3"><input type="number" name="modes[{{ $mode->id }}][ordre]" value="{{ $mode->ordre }}" min="0" class="w-24 rounded border-gray-300"></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <button class="mt-5 px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Enregistrer</button>
            </form>

            <form method="POST" action="{{ route('modes_paiement.store', $entreprise) }}" class="bg-white shadow rounded p-6">
                @csrf
                <h3 class="font-semibold mb-3">Ajouter un moyen</h3>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input name="nom" required placeholder="Ex. Chèque" class="flex-1 rounded border-gray-300">
                    <input type="number" name="ordre" min="0" value="100" class="w-24 rounded border-gray-300">
                    <button class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-900">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
