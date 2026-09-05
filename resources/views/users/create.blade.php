
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ajouter un utilisateur
        </h2>
    </x-slot>
    <div class="py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <form action="{{ route('users.store', $entreprise->id) }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="name" class="block text-gray-700 font-medium mb-1">Nom</label>
                    <input type="text" name="name" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('name') }}" required>
                    @error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                    <input type="email" name="email" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('email') }}" required>
                    @error('email')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-gray-700 font-medium mb-1">Mot de passe</label>
                        <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        @error('password')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-gray-700 font-medium mb-1">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    </div>
                </div>
                <div>
                    <label for="role" class="block text-gray-700 font-medium mb-1">Rôle</label>
                    <select name="role" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="caissier">Caissier</option>
                        <option value="caissier1">Caissier 1</option>
                        <option value="caissier2">Caissier 2</option>
                        <option value="cuisinière">Cuisinière</option>
                        <option value="serveuse">Serveuse</option>
                        <option value="Administrateur">Administrateur</option>
                    </select>
                    @error('role')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="code_pin" class="block text-gray-700 font-medium mb-1">Code PIN (4 chiffres)</label>
                    <input type="text" name="code_pin" maxlength="4" inputmode="numeric" pattern="\d{4}" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('code_pin') }}">
                    @error('code_pin')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="point_de_vente_ids" class="block text-gray-700 font-medium mb-1">Points de vente visibles</label>
                    <select name="point_de_vente_ids[]" multiple class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 min-h-[140px]">
                        @foreach($pointsDeVente as $pointDeVente)
                            <option value="{{ $pointDeVente->id }}" @selected(in_array($pointDeVente->id, old('point_de_vente_ids', [])))>
                                {{ $pointDeVente->nom }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs points de vente.</p>
                    @error('point_de_vente_ids')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                    @error('point_de_vente_ids.*')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="flex justify-end gap-3">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded shadow">Créer</button>
                    <a href="{{ route('users.show', $entreprise->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold px-6 py-2 rounded shadow">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
