@extends('layouts.appsalle')
@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-6"
     x-data='{
        showAddModal: false,
        showEditModal: false,
        showDeleteModal: false,
        loading: false,
        errors: {},
        categories: JSON.parse(`@json(\App\Models\Categorie::where("entreprise_id", $entreprise->id)->get(["id","nom"]))`),
        form: { categorie_id: "", nom: "", image: null, description: "", prix_achat: "", prix_vente: "" },
        editForm: { id: null, categorie_id: "", nom: "", image: null, description: "", prix_achat: "", prix_vente: "" },
        deleteId: null,
        openAdd() {
            this.form = { categorie_id: "", nom: "", image: null, description: "", prix_achat: "", prix_vente: "" };
            this.errors = {};
            this.showAddModal = true;
        },
        openEdit(produit) {
            this.editForm = {
                id: produit.id,
                categorie_id: produit.categorie_id,
                nom: produit.nom,
                image: null,
                description: produit.description,
                prix_achat: produit.prix_achat,
                prix_vente: produit.prix_vente
            };
            this.errors = {};
            this.showEditModal = true;
        },
        openDelete(id) {
            this.deleteId = id;
            this.showDeleteModal = true;
        },
        submitProduit() {
            this.loading = true;
            this.errors = {};
            let formData = new FormData();
            for (const key in this.form) {
                if (key === "image" && this.form.image) formData.append("image", this.form.image);
                else if (key !== "image") formData.append(key, this.form[key]);
            }
            fetch(`{{ route("produits.store", $entreprise) }}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: formData
            })
            .then(async res => {
                const contentType = res.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
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
            .catch(() => alert("Erreur lors de la création."))
            .finally(() => this.loading = false);
        },
        submitEditProduit() {
            this.loading = true;
            this.errors = {};
            let formData = new FormData();
            for (const key in this.editForm) {
                if (key === "image" && this.editForm.image) formData.append("image", this.editForm.image);
                else if (key !== "image") formData.append(key, this.editForm[key]);
            }
            formData.append("_method", "PUT");
            fetch(`/entreprises/{{$entreprise->id}}/produits/${this.editForm.id}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: formData
            })
            .then(async res => {
                const contentType = res.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
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
            .catch(() => alert("Erreur lors de la modification."))
            .finally(() => this.loading = false);
        },
        submitDeleteProduit() {
            this.loading = true;
            fetch(`/entreprises/{{$entreprise->id}}/produits/${this.deleteId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(() => window.location.reload())
            .catch(() => alert("Erreur lors de la suppression."))
            .finally(() => {
                this.loading = false;
                this.showDeleteModal = false;
            });
        }
    }'
>
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
                Nouveau produit
            </button>
        </div>
        <!-- Recherche au centre -->
        <div class="flex-1 flex justify-center order-3 sm:order-none">
            <div class="relative w-full max-w-xs">
                <input id="searchInput" type="text" placeholder="Rechercher par nom, numéro, type..." class="w-full rounded-full border border-gray-300 pl-4 pr-10 py-2 focus:outline-none focus:border-gray-400 shadow-sm" />
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

    <!-- Modal ajout produit -->
    <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8 absolute top-4 left-4">
            <button @click="showAddModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h2 class="text-lg font-bold mb-4 pl-12">Nouveau produit</h2>
            <form @submit.prevent="submitProduit" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                    <select x-model="form.categorie_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Choisir une catégorie</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.nom"></option>
                        </template>
                    </select>
                    <template x-if="errors.categorie_id">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.categorie_id[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                    <input type="text" x-model="form.nom" class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                    <template x-if="errors.nom">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.nom[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Image (optionnelle)</label>
                    <input type="file" @change="form.image = $event.target.files[0]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <template x-if="errors.image">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.image[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea x-model="form.description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    <template x-if="errors.description">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.description[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Prix d'achat</label>
                    <input type="number" step="0.01" x-model="form.prix_achat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <template x-if="errors.prix_achat">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.prix_achat[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Prix de vente</label>
                    <input type="number" step="0.01" x-model="form.prix_vente" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <template x-if="errors.prix_vente">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.prix_vente[0]"></div>
                    </template>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700" :disabled="loading">
                        <span x-show="!loading">Créer</span>
                        <span x-show="loading">...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal modification produit -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8 absolute top-4 left-4">
            <button @click="showEditModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h2 class="text-lg font-bold mb-4 pl-12">Modifier le produit</h2>
            <form @submit.prevent="submitEditProduit" enctype="multipart/form-data">
                <template x-if="editForm.image">
                    <img :src="editForm.image.startsWith('blob:') ? editForm.image : '/storage/' + editForm.image" alt="Image actuelle" class="w-full h-32 object-contain mb-2 rounded">
                </template>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                    <select x-model="editForm.categorie_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Choisir une catégorie</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.nom"></option>
                        </template>
                    </select>
                    <template x-if="errors.categorie_id">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.categorie_id[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                    <input type="text" x-model="editForm.nom" class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                    <template x-if="errors.nom">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.nom[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Image (optionnelle)</label>
                    <input type="file" @change="editForm.image = $event.target.files[0]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <template x-if="errors.image">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.image[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea x-model="editForm.description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    <template x-if="errors.description">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.description[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Prix d'achat</label>
                    <input type="number" step="0.01" x-model="editForm.prix_achat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <template x-if="errors.prix_achat">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.prix_achat[0]"></div>
                    </template>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Prix de vente</label>
                    <input type="number" step="0.01" x-model="editForm.prix_vente" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <template x-if="errors.prix_vente">
                        <div class="text-red-600 text-xs mt-1" x-text="errors.prix_vente[0]"></div>
                    </template>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700" :disabled="loading">
                        <span x-show="!loading">Modifier</span>
                        <span x-show="loading">...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal suppression produit -->
    <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8 absolute top-4 left-4">
            <button @click="showDeleteModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h2 class="text-lg font-bold mb-4 pl-12 text-red-600">Confirmer la suppression</h2>
            <p class="mb-6">Voulez-vous vraiment supprimer ce produit ? Cette action est irréversible.</p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Annuler</button>
                <button type="button" @click="submitDeleteProduit()" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700" :disabled="loading">
                    <span x-show="!loading">Supprimer</span>
                    <span x-show="loading">...</span>
                </button>
            </div>
        </div>
    </div>

  <!-- Vue Grille Améliorée -->
<div id="gridView" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse($produits as $produit)
        <div class="bg-white rounded-xl shadow hover:shadow-lg transition p-4 flex items-start justify-start space-x-3 relative">
            
            {{-- Image du produit à gauche --}}
            <div class="w-20 flex-shrink-0 flex items-center justify-center rounded-lg overflow-hidden">
                <img 
                    src="{{ $produit->image ? asset('storage/' . $produit->image) : asset('images/default-bottle.svg') }}" 
                    alt="Image de {{ $produit->nom }}" 
                    class="w-20 h-20 object-contain rounded-lg"
                >
            </div>

            {{-- Détails du produit à droite --}}
            <div class="flex flex-col justify-start flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        {{ $produit->nom }}
                        <span class="inline-flex items-center rounded-full bg-indigo-100 text-indigo-700 border border-indigo-300 px-2 py-0.5 text-xs font-bold">
                            {{ optional($produit->stockJournalier)->quantite_reste !== null ? optional($produit->stockJournalier)->quantite_reste : '0' }}
                        </span>
                    </h3>
                    {{-- Menu Actions --}}
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            class="p-1 rounded-full hover:bg-gray-100 flex flex-col items-center justify-center"
                        >
                            <span class="block w-1 h-1 bg-gray-600 rounded-full"></span>
                            <span class="block w-1 h-1 bg-gray-600 rounded-full my-0.5"></span>
                            <span class="block w-1 h-1 bg-gray-600 rounded-full"></span>
                        </button>
                        <div 
                            x-show="open" 
                            @click.away="open = false"
                            class="absolute right-0 mt-2 w-40 bg-white border rounded-xl shadow-lg z-10"
                        >
                            <a href="#" 
                               @click.prevent="openEdit({
                                   id: {{ $produit->id }},
                                   categorie_id: '{{ $produit->categorie_id }}',
                                   nom: '{{ addslashes($produit->nom) }}',
                                   image: '{{ $produit->image ?? '' }}',
                                   description: '{{ addslashes($produit->description) }}',
                                   prix_achat: '{{ $produit->prix_achat }}',
                                   prix_vente: '{{ $produit->prix_vente }}'
                               })"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" 
                                     stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" 
                                     stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z"/></svg>
                                Modifier
                            </a>
                            <button 
                                type="button" 
                                @click="openDelete({{ $produit->id }})" 
                                class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-gray-100"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" 
                                     viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" 
                                     stroke-linejoin="round" stroke-width="2" 
                                     d="M6 18L18 6M6 6l12 12"/></svg>
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-gray-600 text-sm mt-1">
                    {{ ucfirst($produit->type) }}
                </div>
                {{-- Ligne prix --}}
                <div class="text-gray-600 text-sm mt-1 flex gap-4">
                    <span>Prix achat : <span class="font-semibold">{{ number_format($produit->prix_achat, 2, ',', ' ') }} F</span></span>
                    <span>Prix vente : <span class="font-semibold">{{ number_format($produit->prix_vente, 2, ',', ' ') }} F</span></span>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center text-gray-500 p-4">Aucun produit trouvé.</div>
    @endforelse
</div>

    <!-- Vue liste -->
    <div id="listView" class="hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-auto bg-white shadow rounded-lg">
                <thead class="bg-gray-100 text-left text-sm font-medium text-gray-600">
                    <tr>
                        <th class="p-3">Nom</th>
                        <th class="p-3">Stock</th>
                        <th class="p-3">Catégorie</th>
                        <th class="p-3">Prix achat</th>
                        <th class="p-3">Prix vente</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produits as $produit)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-3 font-semibold">{{ $produit->nom }}</td>
                            <td class="p-3 font-semibold">
                                {{ optional($produit->stockJournalier)->quantite_reste !== null ? optional($produit->stockJournalier)->quantite_reste : '0' }}
                            </td>
                            <td class="p-3">
                                {{ optional($produit->categorie)->nom ?? '-' }}
                            </td>
                            <td class="p-3">{{ number_format($produit->prix_achat, 2, ',', ' ') }} F</td>
                            <td class="p-3">{{ number_format($produit->prix_vente, 2, ',', ' ') }} F</td>
                            <td class="p-3 flex gap-2">
                                <a href="#" @click.prevent="openEdit({
                                    id: {{ $produit->id }},
                                    categorie_id: '{{ $produit->categorie_id }}',
                                    nom: '{{ addslashes($produit->nom) }}',
                                    image: null,
                                    description: '{{ addslashes($produit->description) }}',
                                    prix_achat: '{{ $produit->prix_achat }}',
                                    prix_vente: '{{ $produit->prix_vente }}'
                                })" class="inline-flex items-center gap-1 bg-indigo-600 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                                    Modifier
                                </a>
                                <button type="button" @click="openDelete({{ $produit->id }})" class="inline-flex items-center gap-1 bg-red-600 text-white px-3 py-1 rounded-md text-sm hover:bg-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    Supprimer
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 p-4">Aucun produit trouvé.</td>
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
            card.style.display = title.includes(value) ? '' : 'none';
        });
        listView.querySelectorAll('tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (!cells.length) return;
            const text = Array.from(cells).slice(0, 4).map(td => td.textContent.trim().toLowerCase()).join(' ');
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>
@endsection