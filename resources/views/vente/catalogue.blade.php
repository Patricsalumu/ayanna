@extends('layouts.appvente')

@section('content')
<div x-data="posApp()" class="flex flex-col md:flex-row gap-4 p-4 min-h-[80vh]">
  <!-- COLONNE GAUCHE : Panier + Options + Pavé numérique -->
  <div class="w-full md:w-1/3 flex flex-col gap-2">
    <!-- Panier -->
    <div class="bg-white rounded-2xl shadow p-1 min-h-0 h-auto" style="padding-top:0.25rem;padding-bottom:0.25rem;">
      <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-semibold flex items-center gap-2">
          🛒 Panier
        </h2>
        <button @click="toggleOptions" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2..."/></svg>
        </button>
      </div>

      <template x-if="panier.length">
        <div class="relative flex flex-col">
          <div
            :class="[
              (mode==='paiement' && panier.length > 5) ? 'overflow-y-auto max-h-[150px]' :
              (selectedIndex!==null && panier.length > 5) ? 'overflow-y-auto max-h-[150px]' :
              (selectedIndex===null && panier.length > 10) ? 'overflow-y-auto max-h-[400px]' :
              'overflow-visible'
            ]"
          >
            <table class="w-full text-sm flex-none">
              <thead>
                <tr class="text-gray-600 border-b">
                  <th class="text-left py-1">Produit</th>
                  <th>Qté</th>
                  <th>Prix</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <template x-for="(item,i) in panierAffiche" :key="item.id">
                  <tr @click="selectItem(i)" :class="{'bg-blue-50': selectedIndex===i}" class="hover:bg-blue-100 cursor-pointer">
                    <td x-text="item.nom" class="py-1"></td>
                    <td class="text-center" x-text="item.qte"></td>
                    <td class="text-right" x-text="item.prix.toLocaleString()+' F'"></td>
                    <td class="text-right" x-text="(item.qte*item.prix).toLocaleString()+' F'"></td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
          <table class="w-full text-sm sticky bottom-0 bg-white">
            <tbody>
              <tr class="font-bold border-t">
                <td colspan="3" class="text-right py-1">Total</td>
                <td class="text-right" x-text="total.toLocaleString()+' F'"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
      <template x-if="!panier.length">
        <div class="flex flex-col items-center mt-4 gap-2">
          <div class="text-gray-400 italic">Votre panier est vide.</div>
          <button @click="libererTable" class="bg-red-600 text-white px-4 py-2 rounded font-bold shadow hover:bg-red-700 transition">Libérer la table</button>
        </div>
      </template>
    </div>

    <!-- Sélecteurs + Options -->
    <div class="bg-white rounded-2xl shadow p-1 min-h-0 h-auto mt-1 mb-0">
      <div class="flex flex-row flex-wrap gap-0.5 mb-4 justify-between items-center overflow-x-auto max-w-full"
        style="min-width:0;">
        <div class="flex flex-1 flex-row flex-wrap gap-0.5 min-w-0">
          <select
            class="flex-1 h-12 min-w-[80px] max-w-[110px] text-base border-0 rounded-xl bg-pink-500 text-white font-bold shadow focus:ring-2 focus:ring-pink-300 transition text-center mx-0.5 px-2 py-0.5 appearance-none
              md:h-10 md:min-w-[70px] md:max-w-[90px] md:text-sm md:px-1 md:py-0.5
              sm:h-9 sm:min-w-[60px] sm:max-w-[80px] sm:text-xs sm:px-0.5 sm:py-0
            "
            style="height:40px;"
            x-model="paiement.client_id"
            @change="setClient(paiement.client_id)"
          >
            <option value="">Client</option>
            @foreach($clients as $c)
              <option value="{{ (string) $c->id }}">{{ $c->nom }}</option>
            @endforeach
          </select>
          <select
            class="flex-1 h-12 min-w-[85px] max-w-[110px] text-base border-0 rounded-xl bg-blue-500 text-white font-bold shadow focus:ring-2 focus:ring-blue-300 transition text-center mx-0.5 px-2 py-0.5 appearance-none
              md:h-10 md:min-w-[70px] md:max-w-[90px] md:text-sm md:px-1 md:py-0.5
              sm:h-9 sm:min-w-[60px] sm:max-w-[80px] sm:text-xs sm:px-0.5 sm:py-0
            "
            style="height:40px;"
            x-model="paiement.serveuse_id"
            @change="setServeuse(paiement.serveuse_id)"
          >
            <option value="">Serveuse</option>
            @foreach($serveuses as $s)
              <option value="{{ (string) $s->id }}">{{ $s->name }}</option>
            @endforeach
          </select>
          <select class="flex-1 h-12 min-w-[80px] max-w-[110px] text-base border-0 rounded-xl bg-yellow-400 text-gray-800 font-bold shadow cursor-not-allowed text-center mx-0.5 px-2 py-0.5 appearance-none
              md:h-10 md:min-w-[70px] md:max-w-[90px] md:text-sm md:px-1 md:py-0.5
              sm:h-9 sm:min-w-[60px] sm:max-w-[80px] sm:text-xs sm:px-0.5 sm:py-0
            " style="height:40px;" disabled>
            @if(isset($tableCourante))
              @php $table = $tables->firstWhere('id', $tableCourante); @endphp
              <option selected>
                @if($table)
                  @if(!empty($table->numero))
                    T{{ $table->numero }}
                  @elseif(!empty($table->nom))
                    {{ $table->nom }}
                  @else
                    Table {{ $table->id }}
                  @endif
                @else
                  Table inconnue
                @endif
              </option>
            @else
              <option selected>Table</option>
            @endif
          </select>
          <button class="flex-1 h-12 min-w-[80px] max-w-[110px] text-base border-0 rounded-xl bg-blue-500 text-white font-bold shadow focus:ring-2 focus:ring-blue-300 transition text-center mx-0.5 px-2 py-0.5 appearance-none
              md:h-10 md:min-w-[70px] md:max-w-[90px] md:text-sm md:px-1 md:py-0.5
              sm:h-9 sm:min-w-[60px] sm:max-w-[80px] sm:text-xs sm:px-0.5 sm:py-0
            " style="height:40px;" @click="openPaiement()">Paiement</button>
        </div>
        <!-- Bouton menu trois points verticaux -->
        <div class="flex-shrink-0 mt-1 md:mt-0">
          <button @click="showModal = true" class="ml-2 flex items-center justify-center w-10 h-12 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 text-2xl font-bold focus:outline-none
              md:w-9 md:h-10 md:text-xl
              sm:w-8 sm:h-9 sm:text-lg
            " title="Options">
            <span style="font-size:2rem;line-height:1;">&#8942;</span>
          </button>
        </div>
        {{-- DEBUG TABLE --}}
        {{-- DEBUG TABLE retiré --}}
      </div>
      <!-- MODALE OPTIONS -->
      <div x-show="showModal" style="background:rgba(0,0,0,0.25)" class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="bg-white rounded-2xl shadow-xl p-6 min-w-[260px] flex flex-col items-center relative">
          <button @click="showModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
          <div class="mb-4 text-lg font-bold text-gray-700">Actions</div>
          <button class="w-full mb-2 py-3 rounded-lg bg-gray-600 text-white font-bold text-base shadow hover:bg-gray-700 transition" @click="printAddition('proforma')">Addition</button>
          <button class="w-full mb-2 py-3 rounded-lg bg-green-600 text-white font-bold text-base shadow hover:bg-green-700 transition">Paiement</button>
          <!-- Bouton Annuler dans la modale -->
          <form method="POST" action="{{ (isset($panier) && !empty($panier->id)) ? route('paniers.annuler', $panier->id) : '#' }}" onsubmit="return confirm('Annuler ce panier ?');" style="width:100%;">
              @csrf
              @method('PATCH')
              <input type="hidden" name="from" value="catalogue">
              <button type="submit" class="w-full py-3 rounded-lg bg-red-600 text-white font-bold text-base shadow hover:bg-red-700 transition">Annuler</button>
          </form>
        </div>
        <div @click="showModal = false" class="fixed inset-0" style="z-index:-1;"></div>
      </div>

    </div>

    <!-- Pavé numérique -->
    <div x-show="selectedIndex!==null || mode==='paiement'" x-transition class="bg-white rounded-2xl shadow p-4">
      <div class="grid grid-cols-4 gap-2">
        <template x-for="btn in touches" :key="btn.label">
          <button @click="mode==='paiement' ? ajouterChiffre(btn.action) : handleKey(btn.action)"
                  class="py-3 rounded text-lg font-semibold"
                  :class="[btn.class, (mode==='paiement' && btn.disabledEnPaiement) ? 'opacity-40 cursor-not-allowed' : '']"
                  :disabled="mode==='paiement' && btn.disabledEnPaiement">
            <span x-text="btn.label"></span>
          </button>
        </template>
      </div>
    </div>
  </div>

  {{-- COLONNE DROITE --}}
  <div class="w-full md:w-2/3 flex flex-col gap-4">
    <template x-if="mode === 'commande'">
      <div>
        {{-- Barre de recherche centrée --}}
        <div class="flex justify-center items-center mb-4">
          <div class="flex w-full max-w-md">
            <input x-model="search" type="text" placeholder="Rechercher un produit..."
                   class="flex-1 px-4 py-3 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"/>
            <button class="bg-blue-600 text-white px-6 py-3 rounded-r-lg hover:bg-blue-700 transition">
              🔍
            </button>
          </div>
        </div>
        {{-- Catégories avec couleurs --}}
        <div class="flex flex-wrap gap-2 mb-4">
          <button @click="selectCat(null)" 
                  :class="!currentCat ? 'bg-gray-600 text-white font-bold ring-2 ring-gray-300' : 'bg-gray-200 text-gray-700'"
                  class="px-4 py-2 rounded-lg transition shadow">
            Toutes
          </button>
          @php
            $colors = [
              'red' => ['bg-red-500', 'text-white', 'ring-red-300', 'bg-red-100', 'text-red-700', 'border-red-400'],
              'blue' => ['bg-blue-500', 'text-white', 'ring-blue-300', 'bg-blue-100', 'text-blue-700', 'border-blue-400'],
              'green' => ['bg-green-500', 'text-white', 'ring-green-300', 'bg-green-100', 'text-green-700', 'border-green-400'],
              'purple' => ['bg-purple-500', 'text-white', 'ring-purple-300', 'bg-purple-100', 'text-purple-700', 'border-purple-400'],
              'yellow' => ['bg-yellow-500', 'text-white', 'ring-yellow-300', 'bg-yellow-100', 'text-yellow-700', 'border-yellow-400'],
              'pink' => ['bg-pink-500', 'text-white', 'ring-pink-300', 'bg-pink-100', 'text-pink-700', 'border-pink-400'],
              'indigo' => ['bg-indigo-500', 'text-white', 'ring-indigo-300', 'bg-indigo-100', 'text-indigo-700', 'border-indigo-400'],
              'teal' => ['bg-teal-500', 'text-white', 'ring-teal-300', 'bg-teal-100', 'text-teal-700', 'border-teal-400'],
              'orange' => ['bg-orange-500', 'text-white', 'ring-orange-300', 'bg-orange-100', 'text-orange-700', 'border-orange-400'],
              'cyan' => ['bg-cyan-500', 'text-white', 'ring-cyan-300', 'bg-cyan-100', 'text-cyan-700', 'border-cyan-400'],
            ];
            $colorKeys = array_keys($colors);
          @endphp
          @foreach($categories as $index => $cat)
            @php
              $colorKey = $colorKeys[$index % count($colorKeys)];
              $colorClasses = $colors[$colorKey];
            @endphp
            <button @click="selectCat({{ $cat->id }})"
                    :class="currentCat==={{ $cat->id }} ? '{{ $colorClasses[0] }} {{ $colorClasses[1] }} font-bold ring-2 {{ $colorClasses[2] }}' : '{{ $colorClasses[3] }} {{ $colorClasses[4] }}'"
                    class="px-4 py-2 rounded-lg transition shadow"
                    data-cat-color="{{ $colorKey }}">
              {{ $cat->nom }}
            </button>
          @endforeach
        </div>

        {{-- Grille catalogue --}}
        <div
          :class="[
            filteredProduits.length > 24 ? 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 xl:grid-cols-7 gap-2 flex-1 overflow-y-auto pr-2 max-h-[440px]' :
            'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 xl:grid-cols-7 gap-2 flex-1 pr-2'
          ]"
        >
          <template x-for="prod in filteredProduits" :key="prod.id">
            <div @click="ajouterProduit(prod)"
                 class="relative bg-white p-2 rounded-xl shadow cursor-pointer hover:ring-2 hover:ring-blue-500 transition h-[102px] min-h-[102px] max-h-[102px] flex flex-col items-center justify-end overflow-hidden">
              
              <!-- Bande colorée en bas selon la catégorie -->
              <div class="absolute bottom-0 left-0 right-0 h-1 z-10"
                   :class="getCategoryColor(prod.categorie_id)"></div>
              
              <div class="relative w-full flex-1 flex flex-col justify-end items-center">
                <template x-if="prod.image">
                  <img :src="prod.image" class="w-[92px] h-[92px] object-cover rounded" style="flex-shrink:0;" />
                </template>
                <template x-if="!prod.image">
                  <div class="w-[92px] h-[92px] rounded flex items-center justify-center" style="flex-shrink:0;">
                    <!-- Icône produit minimaliste noir et blanc -->
                    <svg width="50" height="55" viewBox="0 0 100 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <!-- Corps principal de la bouteille -->
                      <path d="M35 30 L35 95 Q35 102 42 102 L58 102 Q65 102 65 95 L65 30 Q65 27 62 27 L38 27 Q35 27 35 30 Z" 
                            fill="none" stroke="#374151" stroke-width="2.5" stroke-linejoin="round"/>
                      
                      <!-- Goulot -->
                      <rect x="45" y="12" width="10" height="18" fill="none" stroke="#374151" stroke-width="2.5" rx="2" stroke-linejoin="round"/>
                      
                      <!-- Bouchon simple -->
                      <rect x="42" y="8" width="16" height="6" fill="#374151" rx="3"/>
                      
                      <!-- Étiquette épurée -->
                      <rect x="40" y="45" width="20" height="16" fill="none" stroke="#374151" stroke-width="1.5" rx="2"/>
                      
                      <!-- Lignes de texte sur l'étiquette -->
                      <rect x="43" y="49" width="14" height="1.2" fill="#374151" rx="0.6"/>
                      <rect x="45" y="52" width="10" height="1" fill="#6B7280" rx="0.5"/>
                      <rect x="46" y="55" width="8" height="1" fill="#6B7280" rx="0.5"/>
                      <rect x="47" y="57.5" width="6" height="1" fill="#9CA3AF" rx="0.5"/>
                      
                      <!-- Niveau de liquide suggéré -->
                      <path d="M37 32 L37 93 Q37 99 42 99 L58 99 Q63 99 63 93 L63 32" 
                            fill="none" stroke="#9CA3AF" stroke-width="1" stroke-dasharray="2,2" opacity="0.6"/>
                    </svg>
                  </div>
                </template>
                <span class="absolute bottom-0 left-0 right-0 bg-white text-gray-700 text-xs font-semibold truncate px-1 text-center" style="transform:translateY(40%);" x-text="prod.nom"></span>
              </div>
              <template x-if="inqte(prod.id)">
                <div class="absolute top-2 right-2 bg-blue-600 text-white text-xs px-2 py-0.5 rounded-full"
                     x-text="inqte(prod.id)"></div>
              </template>
            </div>
          </template>
        </div>
      </div>
    </template>
    <template x-if="mode === 'paiement'">
      <div class="bg-white rounded-2xl shadow p-4 min-h-0 h-auto relative">
        <button @click="paiement.montantRecu = 0; paiement.monnaie = 0" class="absolute top-2 right-2 text-gray-400 hover:text-red-600 text-2xl font-bold" title="Réinitialiser le montant reçu">&times;</button>
        <div class="mb-4 flex gap-2 justify-center">
          <button @click="paiement.modePaiement = 'espèces'" :class="paiement.modePaiement === 'espèces' ? 'bg-blue-500 text-white font-bold ring-2 ring-blue-300' : 'bg-gray-100 text-gray-700'" class="px-4 py-2 rounded transition">Espèces</button>
          <button @click="paiement.modePaiement = 'mobile_money'" :class="paiement.modePaiement === 'mobile_money' ? 'bg-blue-500 text-white font-bold ring-2 ring-blue-300' : 'bg-gray-100 text-gray-700'" class="px-4 py-2 rounded transition">Mobile Money</button>
          <button @click="paiement.modePaiement = 'compte_client'" :class="paiement.modePaiement === 'compte_client' ? 'bg-blue-500 text-white font-bold ring-2 ring-blue-300' : 'bg-gray-100 text-gray-700'" class="px-4 py-2 rounded transition">Compte Client</button>
        </div>
        <div class="mb-2 text-center">
          <div class="text-lg font-semibold">Montant reçu</div>
          <div class="text-2xl font-bold bg-green-100 text-green-800 rounded-lg px-4 py-2 inline-block" x-text="formatMoney(paiement.montantRecu) + ' FC'"></div>
        </div>
        <div class="mb-2 text-center">
          <span class="text-gray-600">Rendu monnaie :</span>
          <span class="text-xl font-bold text-green-700" x-text="formatMoney(paiement.monnaie) + ' FC'"></span>
        </div>
        <div class="flex justify-between mt-4 gap-2">
          <button @click="mode = 'commande'" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Retour</button>
          <button @click="validerPaiement()" class="px-4 py-2 rounded bg-green-600 text-white font-bold shadow hover:bg-green-700 transition">Valider</button>
          <button @click="validerEtImprimer()" class="px-4 py-2 rounded bg-blue-600 text-white font-bold shadow hover:bg-blue-700 transition">Valider et imprimer</button>
        </div>
      </div>
    </template>
  </div>
</div>
<!-- Ticket d'addition imprimable (généré dynamiquement) -->
<div id="ticket-addition" style="display:none;"></div>
@vite(['resources/js/app.js'])
<script>
window.PRODUITS_ARRAY = @json($produitsArray);
window.PANIER_ARRAY = @json($produitsPanier);
window.CLIENT_ID = @json($client_id ?? '');
window.SERVEUSE_ID = @json($serveuse_id ?? '');
window.CSRF_TOKEN = '{{ csrf_token() }}';
window.TABLE_COURANTE = "{{ $tableCourante ? (int)$tableCourante : '' }}";
window.POINT_DE_VENTE_ID = "{{ $pointDeVente->id ?? '' }}";
window.SET_CLIENT_URL = "{{ url('/panier/set-client') }}";
window.SET_SERVEUSE_URL = "{{ url('/panier/set-serveuse') }}";
window.PANIER_ID = @json($panier->id ?? ($panier['id'] ?? null));
window.ENTREPRISE = @json($pointDeVente->entreprise);
window.CLIENTS = @json($clientsArray ?? []);
window.SERVEUSES = @json($serveusesArray ?? []);
window.MODES_PAIEMENT = @json($modesPaiementArray ?? []);
window.TABLE_COURANTE_LABEL = "{{ $tables->firstWhere('id', $tableCourante)->numero ?? $tables->firstWhere('id', $tableCourante)->nom ?? $tableCourante }}";
window.POINT_DE_VENTE_NOM = "{{ $pointDeVente->nom ?? '' }}";

// Mapping des couleurs des catégories
window.CATEGORY_COLORS = {
  @foreach($categories as $index => $cat)
    @php
      $colors = [
        'red' => 'bg-red-500',
        'blue' => 'bg-blue-500', 
        'green' => 'bg-green-500',
        'purple' => 'bg-purple-500',
        'yellow' => 'bg-yellow-500',
        'pink' => 'bg-pink-500',
        'indigo' => 'bg-indigo-500',
        'teal' => 'bg-teal-500',
        'orange' => 'bg-orange-500',
        'cyan' => 'bg-cyan-500'
      ];
      $colorKeys = array_keys($colors);
      $colorKey = $colorKeys[$index % count($colorKeys)];
      $colorClass = $colors[$colorKey];
    @endphp
    {{ $cat->id }}: '{{ $colorClass }}',
  @endforeach
};

// Fonction pour obtenir la couleur d'une catégorie
window.getCategoryColor = function(categoryId) {
  return window.CATEGORY_COLORS[categoryId] || 'bg-gray-400';
};

// Ajoute ici toutes les autres variables nécessaires à posApp
</script>
@endsection
