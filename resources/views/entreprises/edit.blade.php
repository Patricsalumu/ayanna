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

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone', $entreprise->telephone) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $entreprise->adresse) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Ville</label>
                    <input type="text" name="ville" value="{{ old('ville', $entreprise->ville) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Pays</label>
                    <input type="text" name="pays" value="{{ old('pays', $entreprise->pays) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Slogan</label>
                    <input type="text" name="slogan" value="{{ old('slogan', $entreprise->slogan) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Site web</label>
                    <input type="text" name="site_web" value="{{ old('site_web', $entreprise->site_web) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Identifiant fiscale</label>
                    <input type="text" name="identifiant_fiscale" value="{{ old('identifiant_fiscale', $entreprise->identifiant_fiscale) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Registre de commerce</label>
                    <input type="text" name="registre_commerce" value="{{ old('registre_commerce', $entreprise->registre_commerce) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Numéro d'entreprise</label>
                    <input type="text" name="numero_entreprise" value="{{ old('numero_entreprise', $entreprise->numero_entreprise) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Numéro TVA</label>
                    <input type="text" name="numero_tva" value="{{ old('numero_tva', $entreprise->numero_tva) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $entreprise->email) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
