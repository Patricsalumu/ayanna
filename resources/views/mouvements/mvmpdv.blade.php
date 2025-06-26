<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
        <!-- Header avec gradient -->
        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 shadow-xl">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Navigation et titre -->
                    <div class="flex items-center gap-4">
                        <a href="{{ route('vente.catalogue', ['pointDeVente' => $pointDeVente->id]) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i data-lucide="arrow-left" class="w-5 h-5"></i>
                            <span class="font-medium">Retour au catalogue</span>
                        </a>
                        <div class="flex flex-col">
                            <h1 class="text-2xl lg:text-3xl font-bold text-white flex items-center gap-3">
                                <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <i data-lucide="cash-register" class="w-6 h-6"></i>
                                </div>
                                Entrées/Sorties - {{ $pointDeVente->nom }}
                            </h1>
                            <p class="text-blue-100 mt-1 font-medium">{{ now()->format('d/m/Y') }} - Mouvements du jour</p>
                        </div>
                    </div>

                    <!-- Action principale -->
                    <div class="flex gap-3">
                        <button onclick="openAddModal()" 
                                class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl font-medium">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                            Nouveau mouvement
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Messages de succès -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Cartes de statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Total Entrées -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 hover:shadow-2xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total entrées</p>
                            <p class="text-4xl font-bold text-emerald-600 mt-2">
                                {{ number_format($totalEntree, 0, ',', ' ') }} F
                            </p>
                            <p class="text-sm text-emerald-600 mt-2 font-medium">Argent reçu aujourd'hui</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl">
                            <i data-lucide="trending-up" class="w-8 h-8 text-white"></i>
                        </div>
                    </div>
                    <div class="mt-4 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-green-600 rounded-full" style="width: 85%"></div>
                    </div>
                </div>

                <!-- Total Sorties -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 hover:shadow-2xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total sorties</p>
                            <p class="text-4xl font-bold text-red-500 mt-2">
                                {{ number_format($totalSortie, 0, ',', ' ') }} F
                            </p>
                            <p class="text-sm text-red-500 mt-2 font-medium">Argent dépensé aujourd'hui</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl">
                            <i data-lucide="trending-down" class="w-8 h-8 text-white"></i>
                        </div>
                    </div>
                    <div class="mt-4 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-red-500 to-pink-600 rounded-full" style="width: 65%"></div>
                    </div>
                </div>
            </div>

            <!-- Solde net -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 mb-8">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Solde net du jour</p>
                    <p class="text-5xl font-bold mb-2 {{ ($totalEntree - $totalSortie) >= 0 ? 'text-blue-600' : 'text-red-500' }}">
                        {{ number_format($totalEntree - $totalSortie, 0, ',', ' ') }} F
                    </p>
                    <div class="flex items-center justify-center gap-2">
                        @if(($totalEntree - $totalSortie) >= 0)
                            <i data-lucide="trending-up" class="w-5 h-5 text-emerald-500"></i>
                            <span class="text-emerald-600 font-medium">Résultat positif</span>
                        @else
                            <i data-lucide="trending-down" class="w-5 h-5 text-red-500"></i>
                            <span class="text-red-500 font-medium">Résultat négatif</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Table des mouvements -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <i data-lucide="list" class="w-5 h-5 text-blue-600"></i>
                        Historique des mouvements
                    </h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Compte</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Libellé</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Heure</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($mouvements as $mvt)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $mvt->compte->nom ?? 'Compte supprimé' }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ $mvt->compte->numero ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-900 font-medium">{{ $mvt->libele }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-lg font-bold {{ $mvt->compte && $mvt->compte->type === 'actif' ? 'text-emerald-600' : 'text-red-500' }}">
                                            {{ number_format($mvt->montant, 2, ',', ' ') }} F
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm text-gray-600 font-mono">
                                            {{ $mvt->created_at->format('H:i:s') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($mvt->compte && $mvt->compte->type === 'actif')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <i data-lucide="arrow-down-circle" class="w-4 h-4 mr-1"></i>
                                                Entrée
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                <i data-lucide="arrow-up-circle" class="w-4 h-4 mr-1"></i>
                                                Sortie
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mb-4"></i>
                                            <p class="text-gray-500 text-lg font-medium">Aucun mouvement aujourd'hui</p>
                                            <p class="text-gray-400 text-sm mt-1">Commencez par ajouter un nouveau mouvement</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Mouvement -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all duration-300 scale-95 opacity-0" id="addModalContent">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i data-lucide="plus" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        Nouveau mouvement
                    </h3>
                    <button onclick="closeAddModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        <i data-lucide="x" class="w-5 h-5 text-gray-400"></i>
                    </button>
                </div>
            </div>

            <!-- Info explicative -->
            <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                <div class="flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-500 mt-0.5"></i>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium mb-2">Comment ça fonctionne :</p>
                        <ul class="space-y-1 text-xs">
                            <li><strong>Compte Actif</strong> (ex: Banque, Caisse) → Entrée d'argent</li>
                            <li><strong>Compte Passif</strong> (ex: Charges, Dépenses) → Sortie d'argent de la caisse</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="{{ route('mouvements.pdv.store', $pointDeVente->id) }}" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Compte</label>
                        <select name="compte_id" 
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            <option value="">-- Sélectionner un compte --</option>
                            @foreach($comptes as $compte)
                                <option value="{{ $compte->id }}">
                                    {{ $compte->nom }} ({{ $compte->numero }}) - {{ ucfirst($compte->type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Montant</label>
                        <input type="number" 
                               step="0.01" 
                               name="montant" 
                               required
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="0.00">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Libellé</label>
                        <input type="text" 
                               name="libele" 
                               required
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Description du mouvement...">
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" 
                            onclick="closeAddModal()"
                            class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-all duration-200 font-medium">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl transition-all duration-200 font-medium shadow-lg">
                        Ajouter le mouvement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
    lucide.createIcons();

    // Gestion de la modal
    function openAddModal() {
        const modal = document.getElementById('addModal');
        const content = document.getElementById('addModalContent');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeAddModal() {
        const modal = document.getElementById('addModal');
        const content = document.getElementById('addModalContent');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Fermer la modal en cliquant à l'extérieur
    document.getElementById('addModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddModal();
    });

    // Échapper pour fermer la modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
        }
    });

    // Animation d'entrée pour les cartes
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.transform = 'translateY(0)';
                entry.target.style.opacity = '1';
            }
        });
    }, observerOptions);

    // Observer les cartes pour l'animation
    document.querySelectorAll('.bg-white').forEach(card => {
        card.style.transform = 'translateY(20px)';
        card.style.opacity = '0';
        card.style.transition = 'all 0.6s ease-out';
        observer.observe(card);
    });
    </script>
</x-app-layout>