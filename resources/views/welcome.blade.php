<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayanna | Accueil</title>
    <link rel="stylesheet" href="/build/assets/app-Dz7X2YIF.css">
    <script type="module" src="/build/assets/app-DLcFWqMV.js"></script>
    <!-- Alpine.js pour les interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="bg-[#f5f3ef] text-[#4e342e]">

    <!-- En-tête -->
    <header class="w-full bg-white shadow py-4 px-6 flex justify-between items-center fixed top-0 z-50">
        <div class="text-2xl font-bold">Ayanna</div>
        <nav class="hidden md:flex space-x-6 text-sm font-semibold">
            <a href="#services" class="hover:text-[#a1887f]">Applications</a>
            <a href="#tarifs" class="hover:text-[#a1887f]">Tarification</a>
            <a href="#contact" class="hover:text-[#a1887f]">Contact</a>
        </nav>
        @auth
            <!-- Menu utilisateur connecté -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-2 text-sm font-semibold hover:text-[#a1887f]">
                    <span>{{ Auth::user()->name }}</span>
                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50" style="display: none;">
                    <div class="py-1">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            {{ __('Profil') }}
                        </a>
                        <a href="{{ Auth::user()->entreprise_id ? route('entreprises.show', Auth::user()->entreprise_id) : route('entreprises.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            {{ Auth::user()->entreprise_id ? Auth::user()->entreprise->nom : __('Créer mon entreprise') }}
                        </a>
                        <div class="border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Déconnexion') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <!-- Liens connexion/inscription pour utilisateurs non connectés -->
            <div class="space-x-2 text-sm">
                <a href="{{ route('login') }}" class="hover:text-[#a1887f]">Connexion</a>
                <a href="{{ route('register') }}" class="bg-[#a1887f] text-white px-4 py-2 rounded hover:bg-[#8d6e63]">Inscription</a>
            </div>
        @endauth
    </header>

    <!-- Héros -->
    <section class="pt-32 pb-20 px-6 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">Solutions Cloud modernes pour votre Entreprise</h1>
        <p class="text-lg md:text-xl mb-8 max-w-3xl mx-auto">Des outils informatiques pour piloter votre entreprise avec simplicité, sécurité et efficacité.</p>
        <a href="#services" class="bg-[#a1887f] text-white px-6 py-3 rounded hover:bg-[#8d6e63] font-semibold">Commencer gratuitement</a>
    </section>

    <!-- Services -->
    <section id="services" class="py-20 bg-white text-center px-6">
        <h2 class="text-3xl font-bold mb-10">Applications</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <div class="p-6 border rounded shadow">
                <h3 class="text-xl font-semibold mb-2">POS Restaubar</h3>
                <p>Gérez vos ventes en temps réel, vos salles et vos tables avec efficacité.</p>
            </div>
            <div class="p-6 border rounded shadow">
                <h3 class="text-xl font-semibold mb-2">Stock</h3>
                <p>Suivez vos entrées, sorties et alertes de stock avec simplicité.</p>
            </div>
            <div class="p-6 border rounded shadow">
                <h3 class="text-xl font-semibold mb-2">GRH</h3>
                <p>Gérez votre personnel, présences, paies et fiches d’évaluation.</p>
            </div>
        </div>
    </section>

    <!-- Tarifs -->
    <section id="tarifs" class="py-20 px-6 text-center bg-[#f5f3ef]">
        <h2 class="text-3xl font-bold mb-10">Nos Tarifs</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-6xl mx-auto">
            <div class="border rounded-lg shadow p-6 bg-white">
                <h3 class="text-xl font-semibold mb-2">Essentiel</h3>
                <p class="text-2xl font-bold mb-4">Gratuit</p>
                <ul class="text-sm space-y-2">
                    <li>1 module inclus</li>
                    <li>1 Mois d'utilisation</li>
                    <li>Support communautaire</li>
                </ul>
            </div>
            <div class="border rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-2">Discover</h3>
                <p class="text-2xl font-bold mb-4">3$/Mois</p>
                <ul class="text-sm space-y-2">
                    <li>Tous les module </li>
                    <li>Dependant de votre chiffre d'affaire</li>
                    <li>Support communautaire</li>
                </ul>
            </div>
            <div class="border rounded-lg shadow p-6 bg-white">
                <h3 class="text-xl font-semibold mb-2">Smart</h3>
                <p class="text-2xl font-bold mb-4">9$/mois</p>
                <ul class="text-sm space-y-2">
                    <li>Tous les modules</li>
                    <li>Multi-points de vente</li>
                    <li>Support prioritaire</li>
                </ul>
            </div>
            <div class="border rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-2">Master</h3>
                <p class="text-2xl font-bold mb-4">Sur devis</p>
                <ul class="text-sm space-y-2">
                    <li>Modules personnalisés</li>
                    <li>Hébergement dédié</li>
                    <li>Assistance 24h/24</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="py-20 px-6 text-center bg-white">
        <h2 class="text-3xl font-bold mb-10">Contactez-nous</h2>
        <p class="mb-4">Une question ? Une démo ? Contactez notre équipe Ayanna.</p>
        <p>Email : <a href="mailto:contact@ayanna.com" class="text-[#a1887f] underline">contact@ayanna.com</a></p>
        <p>Téléphone : <a href="tel:+243123456789" class="text-[#a1887f] underline">+243 123 456 789</a></p>
    </section>

    <!-- Footer -->
    <footer class="bg-[#4e342e] text-white py-6 text-center text-sm">
        &copy; 2025 Ayanna. Tous droits réservés.
    </footer>

</body>
</html>
