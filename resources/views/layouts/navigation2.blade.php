<div x-data="{
    ajoutPdvOpen: false,
    pdvFormHtml: '',
    loading: false,
    comptabiliteOpen: false,
    loadPdvForm() {
        this.loading = true;
        @if(isset($entreprise) && $entreprise)
        fetch('{{ route('pointsDeVente.create', [$entreprise->id, 'module_id' => $module->id ?? 0]) }}', {
        @else
        fetch('{{ route('pointsDeVente.create', [Auth::user()->entreprise_id ?? 1, 'module_id' => $module->id ?? 0]) }}', {
        @endif
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            this.pdvFormHtml = html;
            this.loading = false;
        });
    }
}">
<nav class="bg-white border-b border-gray-200">
    <div class="max-w-full px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
        <!-- LOGO + MENUS -->
        <div class="flex items-center space-x-8">
            <a href="{{ route('entreprises.show', Auth::user()->entreprise_id) }}" class="flex items-center">
                <x-application-logo class="h-9 w-auto fill-current text-gray-800" />
            </a>
            <div class="hidden sm:flex space-x-2">
                <div class="flex items-center space-x-0">
                    <!-- Lien retour -->
                    <a href="{{ route('entreprises.show', Auth::user()->entreprise_id) }}" title="Retour" class="text-gray-600 hover:text-gray-800">
                        <!-- Icône de retour (flèche) -->
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <!-- Lien Dashboard -->
                    <a href="{{ url()->previous() }}" class="text-gray-600 hover:text-gray-800">Tableau de board</a>
                    @if(isset($module) && $module)
                    <!-- Bouton Ajout (modale AJAX) -->
                    <button @click="ajoutPdvOpen = true; loadPdvForm()" type="button" title="Ajouter" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                    @endif
                </div>
                <a href="{{ route('salles.show', Auth::user()->entreprise_id) }}" class="text-gray-600 hover:text-gray-800">Salles</a>
                <a href="{{ route('categories.show', Auth::user()->entreprise_id) }}" class="text-gray-600 hover:text-gray-800">Catégories</a>
                <a href="{{ route('produits.entreprise', Auth::user()->entreprise_id) }}" class="text-gray-600 hover:text-gray-800">Produits</a>
                <a href="{{ route('classes-comptables.index') }}" class="text-gray-600 hover:text-gray-800">Classes Comptables</a>
                <a href="{{ route('comptes.index') }}" class="text-gray-600 hover:text-gray-800"> Plan Comptable</a>
                <a href="{{ route('clients.show', Auth::user()->entreprise_id) }}" class="text-gray-600 hover:text-gray-800">Clients</a>
                <a href="{{ route('users.show', Auth::user()->entreprise_id) }}" class="text-gray-600 hover:text-gray-800">Utilisateurs</a>
                
                <!-- Menu Comptabilité avec dropdown -->
                <div class="relative" x-data="{ comptabiliteOpen: false }">
                    <button @click="comptabiliteOpen = !comptabiliteOpen" class="flex items-center space-x-1 text-gray-600 hover:text-gray-800 focus:outline-none">
                        <span>Comptabilité</span>
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20" :class="{'rotate-180': comptabiliteOpen}">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="comptabiliteOpen" 
                         @click.away="comptabiliteOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg border border-gray-200 z-50"
                         style="display: none;">
                        <div class="py-1">
                            <!-- Rapports comptables -->
                            <a href="{{ route('comptabilite.journal') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-book w-4 h-4 mr-3 text-blue-600"></i>
                                Journal Comptable
                            </a>
                            <a href="{{ route('comptabilite.grand-livre') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-list-alt w-4 h-4 mr-3 text-green-600"></i>
                                Grand Livre
                            </a>
                            <a href="{{ route('comptabilite.balance') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-balance-scale w-4 h-4 mr-3 text-indigo-600"></i>
                                Balance
                            </a>
                            <a href="{{ route('comptabilite.bilan') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-chart-pie w-4 h-4 mr-3 text-blue-600"></i>
                                Bilan
                            </a>
                            <a href="{{ route('comptabilite.compte-resultat') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-chart-line w-4 h-4 mr-3 text-green-600"></i>
                                Compte de Résultat
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <!-- Gestion -->
                            <a href="{{ route('creances.liste') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-credit-card w-4 h-4 mr-3 text-orange-600"></i>
                                Créances
                            </a>
                            <a href="{{ route('comptabilite.configuration-pdv') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-cog w-4 h-4 mr-3 text-purple-600"></i>
                                Configuration
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARRE DE RECHERCHE + TOGGLE VIEW + USER DROPDOWN -->
        <div class="flex items-center space-x-4">
            <!-- User Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800">
                    <span>{{ Auth::user()->name }}</span>
                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false"
                    class="absolute right-0 mt-2 w-48 bg-white rounded shadow">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100">Profil</a>
                    <a href="{{ Auth::user()->entreprise_id ? route('entreprises.edit', Auth::user()->entreprise_id) : route('entreprises.create') }}" class="block px-4 py-2 hover:bg-gray-100">
                        {{ Auth::user()->entreprise_id ? 'Entreprise' : 'Créer mon entreprise' }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button class="w-full text-left px-4 py-2 hover:bg-gray-100">Déconnexion</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- MODALE AJOUT POINT DE VENTE (AJAX, masquée par défaut) -->
<div x-show="ajoutPdvOpen"
     style="display: none;"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div @click.away="ajoutPdvOpen = false" class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 relative">
        <div class="flex items-center justify-between mb-4">
            <img src="{{ asset('storage/logos/favicon.png') }}" alt="Ayanna" class="w-8 h-8 rounded-full shadow border-2 border-white bg-white">
            <button @click="ajoutPdvOpen = false" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <h2 class="text-lg font-bold mb-4 text-gray-700">Ajouter un point de vente</h2>
        <template x-if="loading">
            <div class="text-center py-8 text-gray-500">Chargement...</div>
        </template>
        <div x-html="pdvFormHtml"></div>
    </div>
</div>
</div>
