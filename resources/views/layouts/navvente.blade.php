<nav x-data="{ open: false }"  class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('entreprises.show', [Auth::user()->entreprise_id]) }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Bouton retour vers point de vente -->
                <div class="flex items-center ml-4">
                    @php
                        $entrepriseId = session('entreprise_id') ?? Auth::user()->entreprise_id;
                        $pointDeVenteId = session('point_de_vente_id') ?? (isset($pointDeVente) && is_object($pointDeVente) && method_exists($pointDeVente, 'getAttribute') ? $pointDeVente->id : null);
                    @endphp
                    @if($entrepriseId && $pointDeVenteId)
                    <a href="{{ route('pointsDeVente.show', ['entreprise' => $entrepriseId]) }}" 
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold shadow transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Retour
                    </a>
                    @endif
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @php
                        $salleId = session('salle_id') ?? (isset($salle) && is_object($salle) && method_exists($salle, 'getAttribute') ? $salle->id : null);
                    @endphp
                    @if($entrepriseId && $salleId && $pointDeVenteId)
                    <x-nav-link href="{{ route('salle.plan.vente', ['entreprise' => $entrepriseId, 'salle' => $salleId]) }}?point_de_vente_id={{ $pointDeVenteId }}" :active="request()->routeIs('salle.*')">
                        {{ __('Salle') }}
                    </x-nav-link>
                    @else
                    <x-nav-link href="#" :active="request()->routeIs('salle.*')">
                        {{ __('Salle') }}
                    </x-nav-link>
                    @endif
                </div>
            </div>

                <div class="flex items-center ml-2">
              <!-- Bouton menu navigation (burger) -->
              <div class="relative">
                <button @click="showNavMenu = !showNavMenu" class="flex items-center justify-center w-12 h-12 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 focus:outline-none" title="Menu navigation">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                  </svg>
                </button>
                <!-- Menu navigation déroulant -->
                <div x-show="showNavMenu" @click.away="showNavMenu = false" x-transition
                  class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl z-30 flex flex-col p-2 border border-gray-100">
                  <a href="{{ route('paniers.jour') }}"  class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4 block" title="Paniers du jour">Paniers</a>  
                  <a href="{{ (isset($pointDeVente) && is_object($pointDeVente) && method_exists($pointDeVente, 'getAttribute')) ? route('stock_journalier.index', ['pointDeVente' => $pointDeVente->id]) : '#' }}" class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4 block" title="Fiche Produit">Fiche Produit</a>
                  <a href="{{ (isset($pointDeVente) && is_object($pointDeVente) && method_exists($pointDeVente, 'getAttribute')) ? route('rapport.jour', ['pointDeVenteId' => $pointDeVente->id]) : '#' }}"
                     class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4"
                     title="Rapport du jour">
                     Rapport
                  </a>
                  <a href="{{ route('creances.liste') }}"
                     class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4"
                     title="Créances">
                     Créances
                  </a>
                  <a href="{{ (isset($pointDeVente) && is_object($pointDeVente) && method_exists($pointDeVente, 'getAttribute')) ? route('mouvements.pdv', $pointDeVente->id) : '#' }}"
                     class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4"
                     title="Entrées-sorties">
                     Entrées-sorties
                  </a>
                  @php
                      $hasPanierEnCours = (isset($pointDeVente) && is_object($pointDeVente) && method_exists($pointDeVente, 'getAttribute')) ? \App\Models\Panier::where('point_de_vente_id', $pointDeVente->id)
                          ->where('status', 'en_cours')
                          ->exists() : false;
                  @endphp
                  @if(!$hasPanierEnCours && isset($pointDeVente) && is_object($pointDeVente) && method_exists($pointDeVente, 'getAttribute'))
                  <form action="{{ route('stock_journalier.fermer_session', ['pointDeVente' => $pointDeVente->id]) }}" method="POST" onsubmit="return confirm('Confirmer la fermeture de la session ?');" style="margin:0;">
                      @csrf
                      <button type="submit"
                          class="w-full py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4"
                          title="Fermer la session">
                          Fermer
                      </button>
                  </form>
                  @elseif(isset($pointDeVente) && is_object($pointDeVente) && method_exists($pointDeVente, 'getAttribute'))
                  <button class="w-full py-3 rounded-lg bg-gray-200 text-gray-400 font-bold text-base shadow transition text-left px-4 cursor-not-allowed" title="Impossible de fermer : paniers en cours" disabled>
                      Fermer (paniers en cours)
                  </button>
                  @else
                  <button class="w-full py-3 rounded-lg bg-gray-200 text-gray-400 font-bold text-base shadow transition text-left px-4 cursor-not-allowed" title="Point de vente non disponible" disabled>
                      Point de vente non défini
                  </button>
                  @endif
                </div>
              </div>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="#" :active="request()->routeIs('salle.*')">
                {{ __('Salle') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
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
