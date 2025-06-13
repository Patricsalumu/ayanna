
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modifier l'utilisateur
        </h2>
    </x-slot>
    <div class="py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <form action="{{ route('users.update', [$entreprise->id, $user->id]) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="block text-gray-700 font-medium mb-1">Nom</label>
                    <input type="text" name="name" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                    <input type="email" name="email" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-gray-700 font-medium mb-1">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        @error('password')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div>
                    <label for="role" class="block text-gray-700 font-medium mb-1">Rôle</label>
                    <select name="role" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        <option value="super_admin" @if($user->role=='super_admin') selected @endif>Super Admin</option>
                        <option value="admin" @if($user->role=='admin') selected @endif>Admin</option>
                        <option value="comptoiriste" @if($user->role=='comptoiriste') selected @endif>Comptoiriste</option>
                        <option value="cuisinière" @if($user->role=='cuisinière') selected @endif>Cuisinière</option>
                        <option value="serveuse" @if($user->role=='serveuse') selected @endif>Serveuse</option>
                    </select>
                    @error('role')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="flex justify-end gap-3">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded shadow">Enregistrer</button>
                    <a href="{{ route('users.show', $entreprise->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold px-6 py-2 rounded shadow">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
