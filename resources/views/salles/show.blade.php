@extends('layouts.appsalle')

@section('content')
<div class="max-w-8xl mx-auto p-6 bg-gray-100"
     x-data="{
        showModal: false,
        showEditModal: false,
        showDeleteModal: false,
        loading: false,
        errors: {},
        form: { nom: '' },
        editForm: { id: null, nom: '' },
        deleteId: null,
        openCreate() { this.form = { nom: '' }; this.errors = {}; this.showModal = true; },
        openEdit(salle) { this.editForm = { id: salle.id, nom: salle.nom }; this.errors = {}; this.showEditModal = true; },
        openDelete(id) { this.deleteId = id; this.showDeleteModal = true; },
        submitSalle() {
            this.loading = true;
            this.errors = {};
            fetch(`{{ route('salles.store', $entreprise->id) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(this.form)
            })
            .then(res => res.json())
            .then(data => {
                if(data.errors) {
                    this.errors = data.errors;
                } else {
                    window.location.reload();
                }
            })
            .catch(() => alert('Erreur lors de la création.'))
            .finally(() => this.loading = false);
        },
        submitEditSalle() {
            this.loading = true;
            this.errors = {};
            fetch(`/entreprises/{{ $entreprise->id }}/salles/${this.editForm.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ nom: this.editForm.nom })
            })
            .then(res => res.json())
            .then(data => {
                if(data.errors) {
                    this.errors = data.errors;
                } else {
                    window.location.reload();
                }
            })
            .catch(() => alert('Erreur lors de la modification.'))
            .finally(() => this.loading = false);
        },
        submitDeleteSalle() {
            this.loading = true;
            fetch(`/entreprises/{{ $entreprise->id }}/salles/${this.deleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(() => window.location.reload())
            .catch(() => alert('Erreur lors de la suppression.'))
            .finally(() => this.loading = false);
        }
     }">
    <div class="mb-4 flex gap-2">
        <a href="{{ route('pointsDeVente.show', ['entreprise' => $entreprise->id]) }}@if(session('module_id'))?module_id={{ session('module_id') }}@endif"
           class="inline-flex items-center justify-center rounded-lg bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 text-sm font-semibold shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Retour
        </a>
        <button @click="openCreate()" type="button"
           class="inline-flex items-center justify-center rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 text-sm font-semibold shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle salle
        </button>
    </div>

    <!-- Modal création salle -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8 absolute top-4 left-4">
            <button @click="showModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h2 class="text-lg font-bold mb-4 pl-12">Nouvelle salle</h2>
            <form @submit.prevent="submitSalle">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nom de la salle</label>
                    <input type="text" x-model="form.nom" class="mt-1 block w-full rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required>
                    <template x-if="errors.nom">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.nom[0]"></div>
                    </template>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700" x-bind:disabled="loading">
                        <span x-show="!loading">Créer</span>
                        <span x-show="loading">...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal modification salle -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8 absolute top-4 left-4">
            <button @click="showEditModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h2 class="text-lg font-bold mb-4 pl-12">Modifier la salle</h2>
            <form @submit.prevent="submitEditSalle">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nom de la salle</label>
                    <input type="text" x-model="editForm.nom" class="mt-1 block w-full rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required>
                    <template x-if="errors.nom">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.nom[0]"></div>
                    </template>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded bg-amber-500 text-white hover:bg-amber-600" x-bind:disabled="loading">
                        <span x-show="!loading">Modifier</span>
                        <span x-show="loading">...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal suppression salle -->
    <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8 absolute top-4 left-4">
            <button @click="showDeleteModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h2 class="text-lg font-bold mb-4 pl-12">Confirmer la suppression</h2>
            <p class="mb-6 text-gray-700">Voulez-vous vraiment supprimer cette salle ? Cette action est irréversible.</p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Annuler</button>
                <button @click="submitDeleteSalle()" class="px-4 py-2 rounded bg-rose-600 text-white hover:bg-rose-700" x-bind:disabled="loading">
                    <span x-show="!loading">Supprimer</span>
                    <span x-show="loading">...</span>
                </button>
            </div>
        </div>
    </div>

    @if ($entreprise->salles->count() > 0)
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nom de la salle</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nombre de tables</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($entreprise->salles as $index => $salle)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-800 font-semibold">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-800 flex items-center gap-2">
                                <span>{{ $salle->nom }}</span>
                                <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-300">
                                    <svg class="w-3 h-3 mr-1 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 10a6 6 0 1112 0A6 6 0 014 10zm6-4a4 4 0 100 8 4 4 0 000-8z" /></svg>
                                    {{ $salle->tables->count() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800">
                                <span class="inline-block rounded-full bg-gray-200 text-gray-700 px-3 py-1 text-xs font-semibold shadow">
                                    {{ $salle->tables->count() }} tables
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800 flex gap-2">
                                <a href="{{ route('salle.plan', [$entreprise->id, $salle->id]) }}"
                                   class="inline-flex items-center justify-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 text-sm font-semibold shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4" /></svg>
                                    Plan
                                </a>
                                <a href="#" @click.prevent="openEdit({id: {{ $salle->id }}, nom: '{{ addslashes($salle->nom) }}'})"
                                   class="inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 text-sm font-semibold shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" />
                                    </svg>
                                    Modifier
                                </a>
                                <button @click.prevent="openDelete({{ $salle->id }})"
           class="inline-flex items-center justify-center rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-3 py-1 text-sm font-semibold shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Supprimer
        </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-gray-600 italic">
            Aucune salle n’a encore été enregistrée pour cette entreprise.
        </div>
    @endif
</div>
@endsection
