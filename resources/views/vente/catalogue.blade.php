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
        <div class="flex items-center gap-2">
          @if(in_array(auth()->user()?->role, ['Serveuse', 'serveuse'], true))
            <form method="POST" action="{{ route('logout') }}" class="inline-block">
              @csrf
              <input type="hidden" name="serveuse_logout" value="1">
              <button type="submit" class="rounded bg-gray-800 px-3 py-2 text-white text-xs font-semibold hover:bg-gray-700">
                Déconnexion
              </button>
            </form>
          @endif
          <button @click="toggleOptions" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2..."/></svg>
          </button>
        </div>
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
                    <td class="text-right" x-text="formatMoney(item.prix)"></td>
                    <td class="text-right" x-text="formatMoney(item.qte * item.prix)"></td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
          <table class="w-full text-sm sticky bottom-0 bg-white">
            <tbody>
              <tr class="border-t">
                <td colspan="3" class="text-right py-1">Sous-total</td>
                <td class="text-right" x-text="formatMoney(totalHt)"></td>
              </tr>
              @if(app(\App\Services\PermissionService::class)->canApplyDiscount(auth()->user()))
                <tr class="border-t">
                  <td colspan="3" class="text-right py-1">Remise</td>
                  <td class="text-right">
                    <input x-model.number="remise" type="number" min="0" step="0.01" class="w-full text-right border border-gray-300 rounded px-2 py-1" placeholder="0" />
                  </td>
                </tr>
              @endif
              <tr class="font-bold border-t">
                <td colspan="3" class="text-right py-1">Net à payer</td>
                <td class="text-right" x-text="formatMoney(total)"></td>
              </tr>
              <template x-if="showFEquivalent(total)">
                <tr class="border-t text-xs text-gray-600">
                  <td colspan="3" class="text-right py-1">Équivalent F</td>
                  <td class="text-right" x-text="formatFEquivalent(total)"></td>
                </tr>
              </template>
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

    @if(!app(\App\Services\PermissionService::class)->isCashier(auth()->user()))
      <!-- Bouton Envoyer commande -->
      <template x-if="panier.length">
        <button
          type="button"
          @click="if (!bonCommandeEnCours && !bonCommandePrintEnCours) genererBonCommande()"
          :disabled="bonCommandeEnCours || bonCommandePrintEnCours"
          class="w-full min-h-[62px] rounded-2xl bg-orange-600 text-white font-black text-lg shadow hover:bg-orange-700 transition px-4 py-3 leading-tight disabled:opacity-70 disabled:cursor-not-allowed"
        >
          <span x-show="!bonCommandeEnCours && !bonCommandePrintEnCours" class="block">Envoyer commande</span>
          <span x-show="bonCommandeEnCours || bonCommandePrintEnCours" class="block">Traitement...</span>
        </button>
      </template>
    @endif

    <!-- Sélecteurs + Options -->
    <div class="bg-white rounded-2xl shadow p-1 min-h-0 h-auto mt-1 mb-0">
      <div class="flex flex-row flex-wrap gap-2 mb-4 justify-between items-center">
        <select
          class="flex-none sm:flex-1 w-full sm:w-auto h-12 min-w-[80px] max-w-[110px] text-base border-0 rounded-xl bg-pink-500 text-white font-bold shadow focus:ring-2 focus:ring-pink-300 transition text-center mx-1 px-2 py-0.5 appearance-none"
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
          class="flex-none sm:flex-1 w-full sm:w-auto h-12 min-w-[85px] max-w-[110px] text-base border-0 rounded-xl bg-blue-500 text-white font-bold shadow focus:ring-2 focus:ring-blue-300 transition text-center mx-1 px-2 py-0.5 appearance-none"
          style="height:40px;"
          x-model="paiement.serveuse_id"
          @change="setServeuse(paiement.serveuse_id)"
          @if(Auth::user() && (Auth::user()->role === 'Serveuse' || Auth::user()->role === 'serveuse'))
            disabled
          @endif
        >
          <option value="">Serveuse</option>
          @foreach($serveuses as $s)
            <option value="{{ (string) $s->id }}">{{ $s->name }}</option>
          @endforeach
        </select>
        <select class="flex-none sm:flex-1 w-full sm:w-auto h-12 min-w-[80px] max-w-[110px] text-base border-0 rounded-xl bg-yellow-400 text-gray-800 font-bold shadow cursor-not-allowed text-center mx-1 px-2 py-0.5 appearance-none" style="height:40px;" disabled>
          @if(isset($tableCourante))
            @php
              $table = $tables->firstWhere('id', $tableCourante);
              $salleName = $table?->salle?->nom ?? null;
            @endphp
            <option selected>
              @if($table)
                @if(!empty($table->numero))
                  T{{ $table->numero }}@if($salleName) - {{ $salleName }}@endif
                @elseif(!empty($table->nom))
                  {{ $table->nom }}@if($salleName) - {{ $salleName }}@endif
                @else
                  Table {{ $table->id }}@if($salleName) - {{ $salleName }}@endif
                @endif
              @else
                Table inconnue
              @endif
            </option>
          @else
            <option selected>Table</option>
          @endif
        </select>
        @if(!(Auth::user() && (Auth::user()->role === 'Serveuse' || Auth::user()->role === 'serveuse')))
          <button class="flex-none sm:flex-1 w-full sm:w-auto h-12 min-w-[80px] max-w-[110px] text-base border-0 rounded-xl bg-blue-500 text-white font-bold shadow focus:ring-2 focus:ring-blue-300 transition text-center mx-1 px-2 py-0.5 appearance-none" style="height:40px;" @click="openPaiement()">Paiement</button>
        @endif
      </div>
      <div class="flex flex-row flex-wrap gap-2 mb-2 justify-between items-center">
        <button class="flex-none sm:flex-1 w-full sm:w-auto h-12 min-w-[140px] rounded-xl bg-gray-800 text-white 
        font-bold shadow hover:bg-gray-900 transition text-center px-4 py-0.5" @click="printAddition('proforma')">
        Préfacture</button>
        @if(!in_array(Auth::user()->role ?? null, ['comptoiriste','serveuse']))
          <form method="POST" action="{{ (isset($panier) && !empty($panier->id)) ? route('paniers.annuler', $panier->id) : '#' }}" onsubmit="return confirm('Annuler ce panier ?');" class="flex-none sm:flex-1 w-full sm:w-auto min-w-[140px]">
            @csrf
            @method('PATCH')
            <input type="hidden" name="from" value="catalogue">
            <button type="submit" class="w-full h-12 rounded-xl bg-red-600 text-white font-bold shadow hover:bg-red-700 transition">Annuler</button>
          </form>
        @endif
      </div>
      {{-- DEBUG TABLE --}}
      {{-- DEBUG TABLE retiré --}}
    </div>

    <!-- Pavé numérique -->
    <div x-show="selectedIndex!==null || mode==='paiement'" x-transition class="bg-white rounded-2xl shadow p-4">
      <div class="grid grid-cols-4 gap-2">
        <template x-for="btn in touches" :key="btn.label">
          <button @click="mode==='paiement' ? ajouterChiffre(btn.action) : handleKey(btn.action)"
                  class="py-3 rounded text-lg font-semibold"
                  :class="[btn.class, isKeyDisabled(btn) ? 'opacity-40 cursor-not-allowed' : '']"
                  :disabled="(mode !== 'paiement' && !canAddProducts) || isKeyDisabled(btn)"
              :title="!canAddProducts && mode !== 'paiement' ? 'Modification des produits désactivée pour le caissier' : ''">
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
        <div class="w-full" @submit.prevent>
          @if($tableCourante)
            <input type="hidden" name="table_id" value="{{ $tableCourante }}">
          @endif
          {{-- Barre de recherche centrée --}}
          <div class="mb-4">
            <div class="flex w-full items-center gap-2">
              <div class="flex flex-1 min-w-0 items-center">
                <input x-model.debounce.200ms="search" type="text" value="{{ $search ?? '' }}" placeholder="Rechercher un produit..."
                       class="w-full px-4 py-3 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
                       autocomplete="off"/>
                <button type="button" @click="search = ''" class="bg-blue-600 text-white px-6 py-3 rounded-r-lg hover:bg-blue-700 transition whitespace-nowrap">
                  ✕
                </button>
              </div>

              @php
                $retourPlanUrl = route('salle.plan.vente', [
                    'entreprise' => $pointDeVente->entreprise_id,
                    'salle' => session('salle_id') ?? $pointDeVente->salles->first()?->id,
                    'point_de_vente_id' => $pointDeVente->id,
                ]);
              @endphp
              <a href="{{ $retourPlanUrl }}" class="inline-flex items-center justify-center rounded bg-slate-700 px-4 py-3 text-white text-sm font-semibold hover:bg-slate-800 transition whitespace-nowrap min-w-[120px]">
                Retour plan
              </a>

              <button type="button" @click.prevent="refreshCatalogueFromServer()" class="inline-flex items-center justify-center rounded bg-emerald-600 px-4 py-3 text-white text-sm font-semibold hover:bg-emerald-700 transition whitespace-nowrap min-w-[120px]">
                Rafraîchir
              </button>

              @if(in_array(auth()->user()?->role, ['Serveuse', 'serveuse'], true))
                <button type="button" @click.prevent="logoutServeuse()" class="rounded bg-gray-800 px-4 py-3 text-white text-sm font-semibold hover:bg-gray-700 transition whitespace-nowrap min-w-[120px]">
                  Déconnexion
                </button>
              @endif
            </div>
          </div>
          {{-- Catégories avec couleurs --}}
          <div class="flex flex-wrap gap-2 mb-4">
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
              <button type="button"
                      @click="selectCat({{ $cat->id }})"
                      class="px-4 py-2 rounded-lg transition shadow text-sm font-semibold"
                      :class="currentCat === {{ $cat->id }} ? '{{ $colorClasses[0] }} {{ $colorClasses[1] }} font-bold ring-2 {{ $colorClasses[2] }}' : '{{ $colorClasses[3] }} {{ $colorClasses[4] }}'"
                      data-cat-color="{{ $colorKey }}">
                {{ $cat->nom }}
              </button>
            @endforeach
          </div>
        </div>

        {{-- Grille catalogue --}}
        <div
          :class="[
            filteredProduits.length > 24 ? 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 xl:grid-cols-7 gap-2 flex-1 overflow-y-auto pr-2 max-h-[440px]' :
            'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 xl:grid-cols-7 gap-2 flex-1 pr-2'
          ]"
        >
          <template x-for="prod in filteredProduits" :key="prod.id">
            <div @click="canAddProducts ? ajouterProduit(prod) : null"
                 :class="canAddProducts ? 'cursor-pointer hover:ring-2 hover:ring-blue-500' : 'cursor-not-allowed opacity-60'"
                 class="relative bg-white p-2 rounded-xl shadow transition h-[102px] min-h-[102px] max-h-[102px] flex flex-col items-center justify-end overflow-hidden">
              
              <!-- Bande colorée en bas selon la catégorie -->
              <div class="absolute bottom-0 left-0 right-0 h-1 z-10"
                   :class="getCategoryColor(prod.categorie_id)"></div>
              
              <div class="relative w-full flex-1 flex flex-col justify-end items-center">
                <template x-if="prod.image">
                  <img :src="prod.image" class="w-[92px] h-[92px] object-cover rounded" style="flex-shrink:0;" />
                </template>
                <template x-if="!prod.image">
                  <div class="w-[92px] h-[92px] rounded flex items-center justify-center bg-gray-100 border border-gray-200 p-2" style="flex-shrink:0;">
                    <div class="w-full h-full flex items-center justify-center text-center px-1">
                      <p class="text-[11px] font-semibold text-black leading-tight break-words whitespace-normal" x-text="truncateProductName(prod.nom, 25)"></p>
                    </div>
                  </div>
                </template>
              </div>
              <div class="mt-1 w-full text-center px-1">
                <span class="block text-xs font-semibold text-black truncate" x-text="prod.nom"></span>
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
          <label class="block text-lg font-semibold mb-2">Montant reçu</label>
          <input
            x-model.number="paiement.montantRecu"
            @input="paiement.monnaie = paiement.montantRecu - total"
            type="number"
            min="0"
            step="0.01"
            class="w-full text-center text-2xl font-bold bg-green-100 text-green-800 rounded-lg px-4 py-2 appearance-none border border-green-200 focus:outline-none focus:ring-2 focus:ring-green-300"
            placeholder="Entrez le montant reçu"
          />
        </div>
        <div class="mb-2 text-center">
          <span class="text-gray-600">Rendu monnaie :</span>
          <span class="text-xl font-bold text-green-700" x-text="formatMoney(paiement.monnaie)"></span>
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
window.INITIAL_CATEGORY = @json($categorieActive ?? $categories->first()?->id ?? null);
window.INITIAL_SEARCH = @json($search ?? '');
window.CLIENT_ID = @json($client_id ?? '');
window.SERVEUSE_ID = @json($serveuse_id ?? '');
window.CSRF_TOKEN = '{{ csrf_token() }}';
window.TABLE_COURANTE = "{{ $tableCourante ? (int)$tableCourante : '' }}";
window.POINT_DE_VENTE_ID = "{{ $pointDeVente->id ?? '' }}";
window.ENTREPRISE_ID = @json($pointDeVente->entreprise->id ?? session('entreprise_id') ?? null);
window.SALLE_ID = @json(session('salle_id') ?? null);
window.SET_CLIENT_URL = "{{ url('/panier/set-client') }}";
window.SET_SERVEUSE_URL = "{{ url('/panier/set-serveuse') }}";
window.PANIER_ID = @json($panier->id ?? ($panier['id'] ?? null));
window.USER_ROLE = @json(auth()->user()->role ?? null);
window.CAN_ADD_PRODUCTS = @json(app(\App\Services\PermissionService::class)->canAddProductsToTable(auth()->user()));
window.CAN_APPLY_DISCOUNT = @json(app(\App\Services\PermissionService::class)->canApplyDiscount(auth()->user()));
window.ENTREPRISE = @json($pointDeVente->entreprise);
window.CLIENTS = @json($clientsArray ?? []);
window.SERVEUSES = @json($serveusesArray ?? []);
window.MODES_PAIEMENT = @json($modesPaiementArray ?? []);
@php
  $tableLabel = '';
  if ($table = $tables->firstWhere('id', $tableCourante)) {
      if (!empty($table->numero)) {
          $tableLabel = 'T' . $table->numero;
      } elseif (!empty($table->nom)) {
          $tableLabel = $table->nom;
      } else {
          $tableLabel = 'Table ' . $table->id;
      }
      if ($table->salle?->nom) {
          $tableLabel .= ' - ' . $table->salle->nom;
      }
  } else {
      $tableLabel = $tableCourante ? $tableCourante : '';
  }
@endphp
window.TABLE_COURANTE_LABEL = @json($tableLabel);
window.POINT_DE_VENTE_NOM = @json($pointDeVente->nom ?? '');

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
