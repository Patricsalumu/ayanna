<x-app-layout>
<div x-data="posApp()" class="flex flex-col md:flex-row gap-4 p-4 min-h-[80vh]">
  <!-- COLONNE GAUCHE : Panier + Options + Pavé numérique -->
  <div class="w-full md:w-1/3 flex flex-col gap-2">
    <!-- Panier -->
    <div class="bg-white rounded-2xl shadow p-1 min-h-0 h-auto" style="padding-top:0.25rem;padding-bottom:0.25rem;">
      <div class="flex justify-between items-center mb-2">
        <a href="{{ route('salle.plan.vente', [
            'entreprise' => $pointDeVente->entreprise_id,
            'salle' => optional($tables->firstWhere('id', $tableCourante))->salle_id ?? ($tables->first()?->salle_id ?? 1),
            'point_de_vente_id' => $pointDeVente->id
        ]) }}"
           class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-100 text-blue-700 font-semibold text-sm shadow transition"
           title="Retour au plan des tables">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
          <span>Plan des tables</span>
        </a>
        <h2 class="text-xl font-semibold flex items-center gap-2">
          <a href="{{ route('paniers.jour') }}" class="ml-2 px-3 py-1 rounded bg-blue-600 text-white text-sm font-semibold shadow hover:bg-blue-700 transition" title="Voir tous les paniers du jour">
          🛒 Panier
          </a>
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
      <div class="flex flex-row gap-0.5 mb-4 justify-between items-center">
        <select
          class="flex-1 h-12 min-w-[80px] max-w-[110px] text-base border-0 rounded-xl bg-pink-500 text-white font-bold shadow focus:ring-2 focus:ring-pink-300 transition text-center mx-1 px-2 py-0.5 appearance-none"
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
          class="flex-1 h-12 min-w-[85px] max-w-[110px] text-base border-0 rounded-xl bg-blue-500 text-white font-bold shadow focus:ring-2 focus:ring-blue-300 transition text-center mx-1 px-2 py-0.5 appearance-none"
          style="height:40px;"
          x-model="paiement.serveuse_id"
          @change="setServeuse(paiement.serveuse_id)"
        >
          <option value="">Serveuse</option>
          @foreach($serveuses as $s)
            <option value="{{ (string) $s->id }}">{{ $s->name }}</option>
          @endforeach
        </select>
        <select class="flex-1 h-12 min-w-[80px] max-w-[110px] text-base border-0 rounded-xl bg-yellow-400 text-gray-800 font-bold shadow cursor-not-allowed text-center mx-1 px-2 py-0.5 appearance-none" style="height:40px;" disabled>
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
        <button class="flex-1 h-12 min-w-[80px] max-w-[110px] text-base border-0 rounded-xl bg-blue-500 text-white font-bold shadow focus:ring-2 focus:ring-blue-300 transition text-center mx-1 px-2 py-0.5 appearance-none" style="height:40px;" @click="openPaiement()">Paiement</button>
        <!-- Bouton menu trois points verticaux -->
        <button @click="showModal = true" class="ml-2 flex items-center justify-center w-10 h-12 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 text-2xl font-bold focus:outline-none" title="Options">
          <span style="font-size:2rem;line-height:1;">&#8942;</span>
        </button>
        {{-- DEBUG TABLE --}}
        {{-- DEBUG TABLE retiré --}}
      </div>
      <!-- MODALE OPTIONS -->
      <div x-show="showModal" style="background:rgba(0,0,0,0.25)" class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="bg-white rounded-2xl shadow-xl p-6 min-w-[260px] flex flex-col items-center relative">
          <button @click="showModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
          <div class="mb-4 text-lg font-bold text-gray-700">Actions</div>
          <button class="w-full mb-2 py-3 rounded-lg bg-gray-600 text-white font-bold text-base shadow hover:bg-gray-700 transition" @click="printAddition">Addition</button>
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
        {{-- Barre de recherche + bouton menu navigation --}}
        <div class="hidden md:flex justify-between items-center mb-4">
          <div class="flex w-full gap-2">
            <div class="flex-1 flex">
              <input x-model="search" type="text" placeholder="Rechercher un produit..."
                     class="flex-1 px-4 py-2 border rounded-l-lg focus:outline-none focus:ring"/>
              <button class="bg-blue-600 text-white px-4 py-2 rounded-r-lg">🔍</button>
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
                  <button class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4" title="Panier">Fiche Produit</button>
                  <button class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4" title="Tables">Commandes</button>
                  <a href="{{ route('creances.liste') }}"
                     class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4"
                     title="Créances">
                     Créances
                  </a>
                  <a href="{{ route('mouvements.pdv', $pointDeVente->id) }}"
                     class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4"
                     title="Entrées-sorties">
                     Entrées-sorties
                  </a>
                  <button class="w-full py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4" title="Entrée/Sortie">Fermer</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        {{-- Catégories --}}
        <div class="flex flex-wrap gap-2 mb-4">
          <button @click="selectCat(null)" :class="!currentCat ? activeCatClass : inactiveCatClass">Toutes</button>
          @foreach($categories as $cat)
            <button @click="selectCat({{ $cat->id }})"
                    :class="currentCat==={{ $cat->id }} ? activeCatClass : inactiveCatClass">
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
                 class="relative bg-white p-2 rounded-xl shadow cursor-pointer hover:ring-2 hover:ring-blue-500 transition h-[102px] min-h-[102px] max-h-[102px] flex flex-col items-center justify-end">
              <div class="relative w-full flex-1 flex flex-col justify-end items-center">
                <img :src="prod.image" class="w-[92px] h-[92px] object-cover rounded" style="flex-shrink:0;" />
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
        <div class="flex justify-between mt-4">
          <button @click="mode = 'commande'" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Retour</button>
          <button @click="validerPaiement()" class="px-4 py-2 rounded bg-green-600 text-white font-bold shadow hover:bg-green-700 transition">Valider</button>
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
window.CLIENTS = @json($clients);
window.SERVEUSES = @json($serveuses);
window.TABLE_COURANTE_LABEL = "{{ $tables->firstWhere('id', $tableCourante)->numero ?? $tables->firstWhere('id', $tableCourante)->nom ?? $tableCourante }}";
window.POINT_DE_VENTE_NOM = "{{ $pointDeVente->nom ?? '' }}";
// Ajoute ici toutes les autres variables nécessaires à posApp
</script>
</div>
</x-app-layout>
