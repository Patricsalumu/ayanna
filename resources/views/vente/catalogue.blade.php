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
                  :class="btn.class">
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
                  <button class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4" title="Commandes">Créances</button>
                  <button class="w-full mb-1 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-base shadow transition text-left px-4" title="Créances">Entrées-sorties</button>
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

{{-- Alpine.js + JS --}}
@php
try {
    if (!isset($produits) || !$produits) {
        throw new Exception('La variable $produits n\'est pas définie ou vide.');
    }
    $produitsArray = $produits->map(function($p){
        return [
            'id'=>$p->id,
            'nom'=>$p->nom,
            'prix'=>$p->prix_vente,
            'image'=> $p->image ? asset('storage/'.$p->image) : null,
            'cat_id'=>$p->categorie_id
        ];
    })->toArray();
    $client_id = isset($client_id) ? (string) $client_id : '';
    $serveuse_id = isset($serveuse_id) ? (string) $serveuse_id : '';
} catch (\Throwable $e) {
    echo '<div style="color:red;font-weight:bold">Erreur PHP catalogue/produits : '.e($e->getMessage()).'</div>';
}
@endphp
<script>
window.MODES_PAIEMENT = @json($modesPaiement);
window.ENTREPRISE = @json($pointDeVente->entreprise);
window.CLIENTS = @json($clients);
window.SERVEUSES = @json($serveuses);
const PANIER_ID = {{ isset($panier) && $panier->id ? $panier->id : 'null' }};
</script>

<script>
function posApp() {
  return {
    showModal: false,
    showNavMenu: false,
    showPaiement: false,
    produits: @json($produitsArray),
    panier: @json($produitsPanier),
    search: '',
    selectedIndex: null,
    showOptions: false,
    currentCat: null,
    client_id: '{{ $client_id }}',
    serveuse_id: '{{ $serveuse_id }}',
    notification: '',
    notificationType: '', // 'success' | 'error' | 'info'
    showNotification(message, type = 'info') {
      this.notification = message;
      this.notificationType = type;
      setTimeout(() => {
        this.notification = '';
        this.notificationType = '';
      }, 3500);
    },
    mode_paiement_id: '',
    mode_paiement_nom: '',
    montant_recu: '',
    renduMonnaie: '',
    mode: 'commande',
    paiement: {
      montantRecu: 0,
      monnaie: 0,
      modePaiement: 'espèces',
      client_id: '',
      serveuse_id: '',
    },
    touches: [
      {label:'1',action:'1',class:'bg-gray-100'},
      {label:'2',action:'2',class:'bg-gray-100'},
      {label:'3',action:'3',class:'bg-gray-100'},
      {label:'Qté',action:'qte',class:'bg-blue-100'},
      {label:'4',action:'4',class:'bg-gray-100'},
      {label:'5',action:'5',class:'bg-gray-100'},
      {label:'6',action:'6',class:'bg-gray-100'},
      {label:'%',action:'remise',class:'bg-yellow-100'},
      {label:'7',action:'7',class:'bg-gray-100'},
      {label:'8',action:'8',class:'bg-gray-100'},
      {label:'9',action:'9',class:'bg-gray-100'},
      {label:'C',action:'C',class:'bg-indigo-100'},
      {label:'+',action:'+',class:'bg-pink-100'},
      {label:'0',action:'0',class:'bg-gray-100'},
      {label:'-',action:'-',class:'bg-gray-100'},
      {label:'x',action:'x',class:'bg-red-100'},
    ],
    get total(){
      return this.panier.filter(item => item.qte > 0).reduce((a,b)=> a+ b.qte*b.prix,0);
    },
    get filteredProduits(){
      return this.produits.filter(p => {
        return (!this.currentCat || p.cat_id===this.currentCat)
          && (!this.search || p.nom.toLowerCase().includes(this.search.toLowerCase()));
      });
    },
    get panierAffiche() {
      return this.panier.filter(item => item.qte !== null && item.qte >= 0);
    },
    inqte(prod_id){
      const i=this.panier.find(i=>i.id===prod_id);
      return i ? i.qte : null;
    },
    selectCat(id){
      this.currentCat = id;
    },
    toggleOptions(){
      this.showOptions = !this.showOptions;
    },
    ajouterProduit(prod){
      const idx = this.panier.findIndex(i => i.id === prod.id);
    
    // On met à jour localement, mais on attend aussi le retour du serveur
    if (idx >= 0) this.panier[idx].qte++;
    else this.panier.push({ ...prod, qte: 1 });

    fetch(`/vente/panier/ajouter/${prod.id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            quantite: 1,
            table_id: "{{ $tableCourante ? (int)$tableCourante : '' }}",
            point_de_vente_id: "{{ $pointDeVente->id ?? '' }}"
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            this.panier = data.panier; // on remplace le panier local par celui renvoyé par Laravel
        } else {
            alert(data.error || "Une erreur s'est produite");
        }
    })
    .catch(err => {
        console.error("Erreur réseau :", err);
        alert("Erreur de connexion avec le serveur");
    });
      
    },
    setClient(id) {
      fetch('{{ url('/panier/set-client') }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          client_id: id ? Number(id) : null,
          table_id: "{{ $tableCourante ? (int)$tableCourante : '' }}",
          point_de_vente_id: "{{ $pointDeVente->id ?? '' }}"
        })
      })
      .then(res => res.json())
      .then(data => {
        if(data.success) {
          alert('Client enregistré !');
        } else {
          alert(data.error || 'Erreur lors de la sélection du client');
        }
      })
      .catch(() => alert('Erreur de connexion'));
    },
    setServeuse(id) {
      fetch('{{ url('/panier/set-serveuse') }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          serveuse_id: id ? Number(id) : null,
          table_id: "{{ $tableCourante ? (int)$tableCourante : '' }}",
          point_de_vente_id: "{{ $pointDeVente->id ?? '' }}"
        })
      })
      .then(res => res.json())
      .then(data => {
        if(data.success) {
          alert('Serveuse enregistrée !');
        } else {
          alert(data.error || 'Erreur lors de la sélection de la serveuse');
        }
      })
      .catch(() => alert('Erreur de connexion'));
    },
    showNotification(msg, isError = false) {
      this.notification = msg;
      clearTimeout(this.notificationTimeout);
      this.notificationTimeout = setTimeout(() => { this.notification = ''; }, 2000);
    },
    selectItem(idx){
      this.selectedIndex = idx;
    },
    handleKey(action){
      if(this.selectedIndex===null) return;
      const item=this.panier[this.selectedIndex];
      if(!item) return;
      let oldQte = item.qte;
      if(!isNaN(action)){
        item.qte = parseInt(`${item.qte}${action}`.slice(0,3));
      } else if(action==='x'){
        if(item.qte >= 10) {
          let qteStr = item.qte.toString();
          item.qte = parseInt(qteStr.slice(0, -1));
        } else if(item.qte > 0) {
          item.qte = 0;
        } else if(item.qte === 0) {
          // Suppression explicite côté backend
          fetch(`/panier/supprimer-produit/${item.id}`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              table_id: "{{ $tableCourante ? (int)$tableCourante : '' }}",
              point_de_vente_id: "{{ $pointDeVente->id ?? '' }}"
            })
          })
          .then(res => res.json())
          .then(data => {
            if(data.success) {
              this.panier = data.panier || [];
              if(this.selectedIndex !== null && this.selectedIndex >= this.panier.length) {
                this.selectedIndex = this.panier.length > 0 ? this.panier.length-1 : null;
              }
            } else {
              alert(data.error || "Erreur lors de la suppression du produit");
            }
          })
          .catch(err => {
            alert("Erreur de connexion avec le serveur");
          });
          return;
        }
        // On ne supprime plus le produit ici, on attend la réponse serveur
      } else if(action==='C') {
        item.qte = 0;
      } else if(action==='+') {
        item.qte++;
      } else if(action==='-') {
        item.qte > 0 ? item.qte-- : 0;
      }
      // Appel AJAX pour MAJ la base si la quantité a changé
      if(item.qte !== oldQte) {
        fetch(`/panier/modifier-produit/${item.id}`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            quantite: item.qte,
            table_id: "{{ $tableCourante ? (int)$tableCourante : '' }}",
            point_de_vente_id: "{{ $pointDeVente->id ?? '' }}"
          })
        })
        .then(res => res.json())
        .then(data => {
          if(data.success) {
            this.panier = data.panier;
            // Correction de selectedIndex après modification du panier
            if(this.selectedIndex !== null && this.selectedIndex >= this.panier.length) {
              this.selectedIndex = this.panier.length > 0 ? this.panier.length-1 : null;
            }
          } else {
            alert(data.error || "Erreur lors de la mise à jour du panier");
          }
        })
        .catch(err => {
          console.error("Erreur réseau :", err);
          alert("Erreur de connexion avec le serveur");
        });
      }
    },
    libererTable() {
      fetch('/panier/liberer', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          table_id: "{{ $tableCourante ? (int)$tableCourante : '' }}",
          point_de_vente_id: "{{ $pointDeVente->id ?? '' }}"
        })
      })
      .then(res => res.json())
      .then(data => {
        if(data.success && data.redirect_url) {
          window.location.href = data.redirect_url;
        } else if(data.success) {
          window.location.reload();
        } else {
          alert(data.error || "Erreur lors de la libération de la table");
        }
      })
      .catch(err => {
        alert("Erreur de connexion avec le serveur");
      });
    },
    // Ajout de la méthode d'édition d'addition directement dans Alpine.js
    printAddition() {
      const panier = this.panier || [];
      const table = "{{ $tables->firstWhere('id', $tableCourante)->numero ?? $tables->firstWhere('id', $tableCourante)->nom ?? $tableCourante }}";
      const pointDeVente = "{{ $pointDeVente->nom ?? 'Point de vente' }}";
      const entreprise = window.ENTREPRISE;
      const client = this.client_id ? (window.CLIENTS?.find?.(c => c.id == this.client_id) ?? null) : null;
      const serveuse = this.serveuse_id ? (window.SERVEUSES?.find?.(s => s.id == this.serveuse_id) ?? null) : null;
      const panierId = PANIER_ID;
      let total = 0;
      let now = new Date();
      let dateStr = now.toLocaleDateString('fr-FR');
      let heureStr = now.toLocaleTimeString('fr-FR');
      let html = `<div style='width:58mm;padding:0;font-family:monospace;'>`;
      // Logo
      if(entreprise.logo) {
        html += `<div style='text-align:center;'><img src='${window.location.origin}/storage/${entreprise.logo}' style='max-width:40px;max-height:40px;margin-bottom:2px;display:block;margin-left:auto;margin-right:auto;'/></div>`;
      }
      // Nom + infos entreprise
      html += `<div style='text-align:center;font-weight:bold;font-size:15px;'>${entreprise.nom ?? ''}</div>`;
      if(entreprise.numero_entreprise) html += `<div style='text-align:center;font-size:11px;'>N° Entreprise : ${entreprise.numero_entreprise}</div>`;
      if(entreprise.email) html += `<div style='text-align:center;font-size:11px;'>${entreprise.email}</div>`;
      if(entreprise.telephone) html += `<div style='text-align:center;font-size:11px;'>${entreprise.telephone}</div>`;
      if(entreprise.adresse) html += `<div style='text-align:center;font-size:11px;'>${entreprise.adresse}</div>`;
      html += `<div style='border-top:1px dashed #222;margin:6px 0;'></div>`;
      // Infos client/serveuse/table/panier
      html += `<div style='font-size:11px;'>Client : <b>${client?.nom ?? '-'}</b></div>`;
      html += `<div style='font-size:11px;'>Servie par : <b>${serveuse?.name ?? '-'}</b></div>`;
      html += `<div style='font-size:11px;'>Table : <b>${table}</b> | Panier n° <b>${panierId ?? '-'}</b></div>`;
      html += `<div style='border-top:1px dashed #222;margin:6px 0;'></div>`;
      // Tableau produits
      html += `<table style='width:100%;font-size:12px;margin:0 auto;'><thead><tr><th style='text-align:left;'>Produit</th><th>Qté</th><th style='text-align:right;'>Total</th></tr></thead><tbody>`;
      panier.filter(item=>item.qte>0).forEach(item => {
        const lineTotal = item.qte * item.prix;
        total += lineTotal;
        html += `<tr><td style='word-break:break-all;'>${item.nom}</td><td style='text-align:center;'>${item.qte}</td><td style='text-align:right;'>${lineTotal.toLocaleString()} F</td></tr>`;
      });
      html += `</tbody></table>`;
      html += `<div style='border-top:1px dashed #222;margin:6px 0;'></div>`;
      html += `<div style='text-align:right;font-size:14px;font-weight:bold;'>TOTAL : ${total.toLocaleString()} F</div>`;
      html += `<div style='text-align:center;font-size:11px;margin-top:10px;'>Merci pour votre visite !</div>`;
      html += `<div style='text-align:center;font-size:10px;margin-top:8px;'>Généré par Ayanna &copy; | ${dateStr} ${heureStr}</div>`;
      html += `</div>`;
      document.getElementById('ticket-addition').innerHTML = html;
      const printWindow = window.open('', '', 'width=900,height=800');
      printWindow.document.write('<html><head><title>Addition</title>');
      printWindow.document.write('<style>body{margin:0;padding:0;}@media print{body{width:58mm!important;}}</style>');
      printWindow.document.write('</head><body >');
      printWindow.document.write(html);
      printWindow.document.write('</body></html>');
      printWindow.document.close();
      printWindow.focus();
      // Attendre 800ms pour laisser le temps au logo de charger avant impression
      setTimeout(()=>{printWindow.print(); printWindow.close();}, 800);
      // Enregistrement du snapshot d'impression
      if (panier.length && PANIER_ID) {
        fetch(`/panier/impression/${PANIER_ID}`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            total: total,
            produits: panier
          })
        })
        .then(res => res.json())
        .then(data => {
          if(data.success) {
            this.showNotification('Facture enregistrée !');
          }
        });
      }
    },
    openPaiement() {
      this.mode = 'paiement'; // Ajout pour déclencher la vue paiement
      this.showPaiement = true;
      this.mode_paiement_id = '';
      this.mode_paiement_nom = '';
      this.montant_recu = '';
      this.renduMonnaie = '';
    },
    // Watcher pour mettre à jour le nom du mode de paiement
    updateModePaiement() {
      const mp = this.modesPaiement?.find?.(m => m.id == this.mode_paiement_id);
      this.mode_paiement_nom = mp ? mp.nom : '';
    },
    // Calcul du rendu monnaie
    calculRenduMonnaie() {
      const total = this.total;
      const recu = parseFloat(this.montant_recu) || 0;
      this.renduMonnaie = recu > total ? (recu - total).toLocaleString() + ' F' : '0 F';
    },
    ajouterChiffre(valeur) {
      if(this.mode !== 'paiement') return;
      if(isNaN(valeur)) return;
      this.paiement.montantRecu = parseInt(this.paiement.montantRecu.toString() + valeur.toString());
      this.paiement.monnaie = this.paiement.montantRecu - this.total;
    },
    // Validation du paiement (à compléter avec appel backend)
    validerPaiement() {
      if (!this.paiement.modePaiement) {
        alert('Veuillez choisir un mode de paiement.');
        return;
      }
      if (!PANIER_ID) {
        alert('Aucun panier actif.');
        return;
      }
      // Debug : afficher les valeurs avant la vérification
      console.log('DEBUG modePaiement:', this.paiement.modePaiement);
      // Vérification obligatoire pour le mode "compte_client" UNIQUEMENT
      if (this.paiement.modePaiement.toLowerCase() === 'compte_client') {
        if (this.paiement.client_id === '' || this.paiement.serveuse_id === '') {
          this.showNotification('Pour un paiement par compte client, vous devez sélectionner un client ET une serveuse.', 'error');
          return;
        }
      }
      const payload = {
        panier_id: PANIER_ID,
        mode_paiement: this.paiement.modePaiement
      };
      fetch('/vente/valider', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(res => res.json())
      .then(data => {
        if(data.success) {
          this.showNotification('Paiement validé et enregistré !', 'success');
          this.panier = [];
          this.paiement.montantRecu = 0;
          this.paiement.monnaie = 0;
          this.mode = 'commande';
        } else {
          this.showNotification(data.error || 'Erreur lors de la validation', 'error');
        }
      })
      .catch(() => alert('Erreur de connexion avec le serveur'));
    },
    // Hook pour surveiller le changement de mode de paiement
    get modesPaiement() {
      return window.MODES_PAIEMENT || [];
    },
    activeCatClass: 'px-4 py-2 rounded-full bg-blue-600 text-white text-sm font-semibold shadow',
    inactiveCatClass: 'px-4 py-2 rounded-full bg-gray-100 hover:bg-gray-100 text-sm font-semibold shadow',
    formatMoney(val) {
      let n = parseInt(val) || 0;
      return n.toLocaleString('fr-FR');
    },
  }
}
</script>

<!-- Ticket d'addition imprimable (généré dynamiquement) -->
<div id="ticket-addition" style="display:none;"></div>
</div>

</x-app-layout>
