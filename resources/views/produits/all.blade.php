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
    <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto relative">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-blue-50 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8">
                    <h2 class="text-xl font-bold text-gray-800">Nouveau produit</h2>
                </div>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            </div>
            
            <!-- Contenu -->
            <div class="p-6">
                <form @submit.prevent="submitProduit" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Colonne gauche -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Catégorie *</label>
                                <select x-model="form.categorie_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Choisir une catégorie</option>
                                    <template x-for="cat in categories" :key="cat.id">
                                        <option :value="cat.id" x-text="cat.nom"></option>
                                    </template>
                                </select>
                                <template x-if="errors.categorie_id">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.categorie_id[0]"></div>
                                </template>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom du produit *</label>
                                <input type="text" x-model="form.nom" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <template x-if="errors.nom">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.nom[0]"></div>
                                </template>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Image (optionnelle)</label>
                                <input type="file" @change="form.image = $event.target.files[0]" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <template x-if="errors.image">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.image[0]"></div>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Colonne droite -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Prix d'achat</label>
                                <input type="number" step="0.01" x-model="form.prix_achat" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00">
                                <template x-if="errors.prix_achat">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.prix_achat[0]"></div>
                                </template>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Prix de vente *</label>
                                <input type="number" step="0.01" x-model="form.prix_vente" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required placeholder="0.00">
                                <template x-if="errors.prix_vente">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.prix_vente[0]"></div>
                                </template>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                <textarea x-model="form.description" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Description du produit..."></textarea>
                                <template x-if="errors.description">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.description[0]"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer avec boutons -->
                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition-colors">
                            Annuler
                        </button>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-medium transition-colors shadow-md" :disabled="loading">
                            <span x-show="!loading" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Créer le produit
                            </span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"/>
                                </svg>
                                Création...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal modification produit -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto relative">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-indigo-50 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8">
                    <h2 class="text-xl font-bold text-gray-800">Modifier le produit</h2>
                </div>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            </div>
            
            <!-- Contenu -->
            <div class="p-6">
                <!-- Image actuelle si existante -->
                <template x-if="editForm.image && !editForm.image.startsWith('blob:')">
                    <div class="mb-6 text-center">
                        <img :src="'/storage/' + editForm.image" alt="Image actuelle" class="max-w-24 h-24 object-contain mx-auto rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-500 mt-1">Image actuelle</p>
                    </div>
                </template>
                
                <form @submit.prevent="submitEditProduit" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Colonne gauche -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Catégorie *</label>
                                <select x-model="editForm.categorie_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Choisir une catégorie</option>
                                    <template x-for="cat in categories" :key="cat.id">
                                        <option :value="cat.id" x-text="cat.nom"></option>
                                    </template>
                                </select>
                                <template x-if="errors.categorie_id">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.categorie_id[0]"></div>
                                </template>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom du produit *</label>
                                <input type="text" x-model="editForm.nom" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <template x-if="errors.nom">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.nom[0]"></div>
                                </template>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Image (optionnelle)</label>
                                <input type="file" @change="editForm.image = $event.target.files[0]" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <template x-if="errors.image">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.image[0]"></div>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Colonne droite -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Prix d'achat</label>
                                <input type="number" step="0.01" x-model="editForm.prix_achat" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="0.00">
                                <template x-if="errors.prix_achat">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.prix_achat[0]"></div>
                                </template>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Prix de vente *</label>
                                <input type="number" step="0.01" x-model="editForm.prix_vente" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="0.00">
                                <template x-if="errors.prix_vente">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.prix_vente[0]"></div>
                                </template>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                <textarea x-model="editForm.description" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Description du produit..."></textarea>
                                <template x-if="errors.description">
                                    <div class="text-red-600 text-xs mt-1" x-text="errors.description[0]"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer avec boutons -->
                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition-colors">
                            Annuler
                        </button>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium transition-colors shadow-md" :disabled="loading">
                            <span x-show="!loading" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Modifier le produit
                            </span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"/>
                                </svg>
                                Modification...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal suppression produit -->
    <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md relative">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-red-50 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('storage/logos/favicon.png') }}" alt="Favicon" class="h-8 w-8">
                    <h2 class="text-xl font-bold text-red-700">Confirmer la suppression</h2>
                </div>
                <button @click="showDeleteModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            </div>
            
            <!-- Contenu -->
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-700 font-medium">Voulez-vous vraiment supprimer ce produit ?</p>
                        <p class="text-sm text-gray-500 mt-1">Cette action est irréversible et supprimera définitivement toutes les données associées.</p>
                    </div>
                </div>
                
                <!-- Footer avec boutons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" @click="showDeleteModal = false" class="px-6 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition-colors">
                        Annuler
                    </button>
                    <button type="button" @click="submitDeleteProduit()" class="px-6 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-medium transition-colors shadow-md" :disabled="loading">
                        <span x-show="!loading" class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer
                        </span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                                <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"/>
                            </svg>
                            Suppression...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

  <!-- Vue Grille Améliorée -->
<div id="gridView" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse($produits as $produit)
        <div class="bg-white rounded-xl shadow hover:shadow-lg transition p-4 flex items-start justify-start space-x-3 relative">
            
            {{-- Image du produit à gauche --}}
            <div class="w-20 flex-shrink-0 flex items-center justify-center rounded-lg overflow-hidden">
                @if($produit->image)
                    <img 
                        src="{{ asset('storage/' . $produit->image) }}" 
                        alt="Image de {{ $produit->nom }}" 
                        class="w-20 h-20 object-contain rounded-lg"
                    >
                @else
                    <div class="w-20 h-20 bg-white rounded-lg flex items-center justify-center border border-gray-200">
                        <!-- Belle bouteille en noir et blanc -->
                        <svg width="45" height="55" viewBox="0 0 120 130" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Corps principal de la bouteille -->
                            <path d="M40 40 L40 110 Q40 120 50 120 L70 120 Q80 120 80 110 L80 40 Q80 35 75 35 L45 35 Q40 35 40 40 Z" 
                                  fill="#F8F9FA" stroke="#374151" stroke-width="1.5"/>
                            
                            <!-- Goulot -->
                            <rect x="52" y="18" width="16" height="22" fill="#F8F9FA" stroke="#374151" stroke-width="1.5" rx="3"/>
                            
                            <!-- Bouchon -->
                            <ellipse cx="60" cy="16" rx="10" ry="4" fill="#6B7280" stroke="#374151" stroke-width="1"/>
                            <rect x="50" y="12" width="20" height="8" fill="#6B7280" stroke="#374151" stroke-width="1" rx="4"/>
                            
                            <!-- Étiquette -->
                            <rect x="45" y="55" width="30" height="25" fill="#FFFFFF" stroke="#374151" stroke-width="1" rx="4"/>
                            
                            <!-- Design sur l'étiquette -->
                            <circle cx="52" cy="63" r="2" fill="#374151"/>
                            <circle cx="68" cy="63" r="2" fill="#374151"/>
                            <path d="M52 65 Q60 70 68 65" stroke="#374151" stroke-width="1.5" fill="none"/>
                            <rect x="48" y="70" width="24" height="1" fill="#374151" rx="0.5"/>
                            <rect x="50" y="73" width="20" height="0.8" fill="#6B7280" rx="0.4"/>
                            <rect x="52" y="75.5" width="16" height="0.8" fill="#6B7280" rx="0.4"/>
                            
                            <!-- Liquide à l'intérieur -->
                            <path d="M42 42 L42 108 Q42 116 50 116 L70 116 Q78 116 78 108 L78 42" 
                                  fill="#E5E7EB"/>
                            
                            <!-- Reflets et brillance -->
                            <path d="M44 40 L44 105 Q44 110 47 110 L49 110 Q46 110 46 105 L46 40" 
                                  fill="#FFFFFF" opacity="0.6"/>
                            <ellipse cx="47" cy="50" rx="2" ry="8" fill="#FFFFFF" opacity="0.4"/>
                            
                            <!-- Bulle d'air -->
                            <circle cx="72" cy="48" r="1.5" fill="#FFFFFF" opacity="0.8"/>
                            <circle cx="69" cy="52" r="1" fill="#FFFFFF" opacity="0.6"/>
                        </svg>
                    </div>
                @endif
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
        
        // Recherche dans la vue grille
        gridView.querySelectorAll('.bg-white.rounded-xl').forEach(card => {
            const title = card.querySelector('h3')?.textContent.trim().toLowerCase() || '';
            const description = card.querySelector('.text-gray-600')?.textContent.trim().toLowerCase() || '';
            const prices = card.querySelector('.flex.gap-4')?.textContent.trim().toLowerCase() || '';
            
            const searchText = `${title} ${description} ${prices}`;
            card.style.display = searchText.includes(value) ? '' : 'none';
        });
        
        // Recherche dans la vue liste
        listView.querySelectorAll('tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (!cells.length) return;
            const text = Array.from(cells).slice(0, 5).map(td => td.textContent.trim().toLowerCase()).join(' ');
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>
@endsection