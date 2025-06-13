<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modifier l'entreprise
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
            @if(session('success'))
                <div class="mb-4 text-green-600">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('entreprises.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Nom de l’entreprise</label>
                    <input type="text" name="nom" value="{{ old('nom', $entreprise->nom) }}" required
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Logo</label>
                    <input type="file" name="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50
                        file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                @if($entreprise->logo)
                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700">Logo actuel</label>
                        <img src="{{ asset('storage/' . $entreprise->logo) }}" alt="Logo" class="w-32 h-auto mt-2">
                    </div>
                @endif

                <div class="flex items-center justify-end">
                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
