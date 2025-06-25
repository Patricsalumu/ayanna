@extends('layouts.appsalle')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-6"
     x-data="{
        showAddModal: false,
        showEditModal: false,
        showDeleteModal: false,
        loading: false,
        errors: {},
        form: { name: '', email: '', role: '', password: '', password_confirmation: '' },
        editForm: { id: null, name: '', email: '', role: '' },
        deleteId: null,
        openAdd() { this.form = { name: '', email: '', role: '', password: '', password_confirmation: '' }; this.errors = {}; this.showAddModal = true; },
        openEdit(user) { this.editForm = { id: user.id, name: user.name, email: user.email, role: user.role }; this.errors = {}; this.showEditModal = true; },
        openDelete(id) { this.deleteId = id; this.showDeleteModal = true; },
        submitUser() {
            this.loading = true;
            this.errors = {};
            fetch(`{{ route('users.store', $entreprise->id) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(this.form)
            })
            .then(async res => {
                const contentType = res.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await res.json();
                    if(data.errors) {
                        this.errors = data.errors;
                    } else {
                        window.location.reload();
                    }
                } else {
                    window.location.reload();
                }
            })
            .catch(() => alert('Erreur lors de la création.'))
            .finally(() => this.loading = false);
        },
        submitEditUser() {
            this.loading = true;
            this.errors = {};
            fetch(`/entreprises/{{ $entreprise->id }}/users/${this.editForm.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(this.editForm)
            })
            .then(async res => {
                const contentType = res.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await res.json();
                    if(data.errors) {
                        this.errors = data.errors;
                    } else {
                        window.location.reload();
                    }
                } else {
                    window.location.reload();
                }
            })
            .catch(() => alert('Erreur lors de la modification.'))
            .finally(() => this.loading = false);
        },
        submitDeleteUser() {
            this.loading = true;
            fetch(`/entreprises/{{ $entreprise->id }}/users/${this.deleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(() => window.location.reload())
            .catch(() => alert('Erreur lors de la suppression.'))
            .finally(() => { this.loading = false; this.showDeleteModal = false; });
        }
     }">
    <!-- Barre de contrôle -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <!-- Boutons ajout et retour à gauche -->
        <div class="flex gap-2 order-1 sm:order-none">
            <a href="{{ route('pointsDeVente.show', $entreprise->id) }}" class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Retour
            </a>
            <button @click="openAdd()" type="button" class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Ajouter
            </button>
        </div>
        <!-- Recherche au centre -->
        <div class="flex-1 flex justify-center order-3 sm:order-none">
            <div class="relative w-full max-w-xs">
                <input id="searchInput" type="text" placeholder="Rechercher un utilisateur..." class="w-full rounded-full border border-gray-300 pl-4 pr-10 py-2 focus:outline-none focus:border-gray-400 shadow-sm" />
                <span class="absolute right-3 top-2.5 text-gray-400">🔍</span>
            </div>
        </div>
        <!-- Boutons de vue à droite -->
        <div class="flex space-x-2 ml-2 order-2 sm:order-none">
            <button id="btnGridView" title="Vue Grille" class="bg-gray-100 rounded p-2 hover:bg-gray-200 text-gray-600 flex items-center justify-center focus:bg-indigo-100 focus:text-indigo-700">
                <i data-lucide="grid"></i>
            </button>
            <button id="btnListView" title="Vue Liste" class="bg-gray-100 rounded p-2 hover:bg-gray-200 text-gray-600 flex items-center justify-center focus:bg-indigo-100 focus:text-indigo-700">
                <i data-lucide="list"></i>
            </button>
        </div>
    </div>

    <!-- Modal ajout utilisateur -->
    <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8 absolute top-4 left-4">
            <button @click="showAddModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h2 class="text-lg font-bold mb-4 pl-12">Nouvel utilisateur</h2>
            <form @submit.prevent="submitUser">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                    <input type="text" x-model="form.name" class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                    <template x-if="errors.name">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.name[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" x-model="form.email" class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                    <template x-if="errors.email">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.email[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Rôle</label>
                    <select x-model="form.role" class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="comptoiriste">Comptoiriste</option>
                        <option value="cuisinière">Cuisinière</option>
                        <option value="serveuse">Serveuse</option>
                    </select>
                    <template x-if="errors.role">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.role[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                    <input type="password" x-model="form.password" class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                    <template x-if="errors.password">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.password[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                    <input type="password" x-model="form.password_confirmation" class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700" x-bind:disabled="loading">
                        <span x-show="!loading">Créer</span>
                        <span x-show="loading">...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal modification utilisateur -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8 absolute top-4 left-4">
            <button @click="showEditModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h2 class="text-lg font-bold mb-4 pl-12">Modifier l'utilisateur</h2>
            <form @submit.prevent="submitEditUser">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                    <input type="text" x-model="editForm.name" class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                    <template x-if="errors.name">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.name[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" x-model="editForm.email" class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                    <template x-if="errors.email">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.email[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Rôle</label>
                    <select x-model="editForm.role" class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="comptoiriste">Comptoiriste</option>
                        <option value="cuisinière">Cuisinière</option>
                        <option value="serveuse">Serveuse</option>
                    </select>
                    <template x-if="errors.role">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.role[0]"></div>
                    </template>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700" x-bind:disabled="loading">
                        <span x-show="!loading">Modifier</span>
                        <span x-show="loading">...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal suppression utilisateur -->
    <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8 absolute top-4 left-4">
            <button @click="showDeleteModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h2 class="text-lg font-bold mb-4 pl-12 text-red-600">Confirmer la suppression</h2>
            <p class="mb-6">Voulez-vous vraiment supprimer cet utilisateur ? Cette action est irréversible.</p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Annuler</button>
                <button type="button" @click="submitDeleteUser()" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700" x-bind:disabled="loading">
                    <span x-show="!loading">Supprimer</span>
                    <span x-show="loading">...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Vue grille -->
    <div id="gridView" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($users as $user)
            <div class="bg-white rounded-lg shadow p-4 relative hover:shadow-lg transition flex flex-col justify-between">
                <div class="absolute top-2 right-2" x-data="{ open: false }">
                    <button @click="open = !open" class="p-1 rounded-full hover:bg-gray-100 flex flex-col items-center justify-center">
                        <span class="block w-1 h-1 bg-gray-700 rounded-full mb-0.5"></span>
                        <span class="block w-1 h-1 bg-gray-700 rounded-full mb-0.5"></span>
                        <span class="block w-1 h-1 bg-gray-700 rounded-full"></span>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg z-10">
                        <a href="#" @click.prevent="openEdit({id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', role: '{{ $user->role }}'})" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                            Modifier
                        </a>
                        <button type="button" @click="openDelete({{ $user->id }})" class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            Supprimer
                        </button>
                    </div>
                </div>
                <h3 class="text-lg font-bold mb-2 flex items-center gap-2">
                    {{ $user->name }}
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-300">
                        {{ $user->role }}
                    </span>
                </h3>
                <div class="text-gray-600 text-sm mb-1">{{ $user->email }}</div>
            </div>
        @endforeach
    </div>

    <!-- Vue liste -->
    <div id="listView" class="hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-auto bg-white shadow rounded-lg">
                <thead class="bg-gray-100 text-left text-sm font-medium text-gray-600">
                    <tr>
                        <th class="p-3">Nom</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Rôle</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-3 font-semibold flex items-center gap-2">
                                {{ $user->name }}
                            </td>
                            <td class="p-3">{{ $user->email }}</td>
                            <td class="p-3">{{ $user->role }}</td>
                            <td class="p-3 flex gap-2">
                                <a href="#" @click.prevent="openEdit({id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', role: '{{ $user->role }}'})" class="inline-flex items-center gap-1 bg-indigo-600 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                                    Modifier
                                </a>
                                <button type="button" @click="openDelete({{ $user->id }})" class="inline-flex items-center gap-1 bg-red-600 text-white px-3 py-1 rounded-md text-sm hover:bg-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    Supprimer
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 p-4">Aucun utilisateur trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>
    lucide.createIcons();
</script>
<script>
    const btnGridView = document.getElementById('btnGridView');
    const btnListView = document.getElementById('btnListView');
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const searchInput = document.getElementById('searchInput');

    function setActiveView(view) {
        if(view === 'grid') {
            btnGridView.classList.add('bg-indigo-100', 'text-indigo-700');
            btnListView.classList.remove('bg-indigo-100', 'text-indigo-700');
        } else {
            btnListView.classList.add('bg-indigo-100', 'text-indigo-700');
            btnGridView.classList.remove('bg-indigo-100', 'text-indigo-700');
        }
    }

    btnGridView?.addEventListener('click', () => {
        gridView.classList.remove('hidden');
        listView.classList.add('hidden');
        setActiveView('grid');
    });
    btnListView?.addEventListener('click', () => {
        listView.classList.remove('hidden');
        gridView.classList.add('hidden');
        setActiveView('list');
    });
    setActiveView('grid');

    searchInput?.addEventListener('input', (e) => {
        const value = e.target.value.trim().toLowerCase();
        gridView.querySelectorAll('.bg-white.rounded-lg').forEach(card => {
            const title = card.querySelector('h3')?.textContent.trim().toLowerCase();
            card.style.display = title.startsWith(value) ? '' : 'none';
        });
        listView.querySelectorAll('tbody tr').forEach(row => {
            const cell = row.querySelector('td');
            if (!cell) return;
            const text = cell.textContent.trim().toLowerCase();
            row.style.display = text.startsWith(value) ? '' : 'none';
        });
    });
</script>
@endsection