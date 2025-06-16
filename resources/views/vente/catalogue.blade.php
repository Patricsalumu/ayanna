<x-app-layout>
<div x-data="posApp()" class="flex flex-col md:flex-row gap-4 p-4 min-h-[80vh]">

  {{-- COLONNE GAUCHE --}}
  <div class="w-full md:w-1/3 flex flex-col gap-4">
  
    <!-- Recherche & filtres petits écrans -->
    <div class="md:hidden mb-2">
      <input x-model="search" type="text" placeholder="Rechercher..."
             class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring"/>
    </div>

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
        <h2 class="text-xl font-semibold">🛒 Panier</h2>
        <button @click="toggleOptions" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2..."/></svg>
        </button>
      </div>

      <template x-if="panier.length">
        <div class="relative">
          <div class="flex flex-col">
            <table class="w-full text-sm flex-none">
              <thead>
                <tr class="text-gray-600 border-b">
                  <th class="text-left py-1">Produit</th>
                  <th>Qté</th>
                  <th>Prix</th>
                  <th>Total</th>
                </tr>
              </thead>
            </table>
            <div :class="
              selectedIndex !== null && panier.length >= 5
                ? 'max-h-40 overflow-y-auto' // 5 lignes visibles (environ 32px/ligne)
                : panier.length >= 15
                  ? 'max-h-[400px] overflow-y-auto' // 15 lignes visibles
                  : ''
            " class="flex-1">
              <table class="w-full text-sm">
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
          <table class="w-full text-sm">
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
          x-model="client_id"
          @change="setClient(client_id)"
        >
          <option value="">Client</option>
          @foreach($clients as $c)
            <option value="{{ (string) $c->id }}">{{ $c->nom }}</option>
          @endforeach
        </select>
        <select
          class="flex-1 h-12 min-w-[85px] max-w-[110px] text-base border-0 rounded-xl bg-blue-500 text-white font-bold shadow focus:ring-2 focus:ring-blue-300 transition text-center mx-1 px-2 py-0.5 appearance-none"
          style="height:40px;"
          x-model="serveuse_id"
          @change="setServeuse(serveuse_id)"
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
        <button class="flex-1 h-12 min-w-[80px] max-w-[110px] text-base border-0 rounded-xl bg-blue-500 text-white font-bold shadow focus:ring-2 focus:ring-blue-300 transition text-center mx-1 px-2 py-0.5 appearance-none" style="height:40px;">Paiement</button>
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
          <button class="w-full mb-2 py-3 rounded-lg bg-gray-600 text-white font-bold text-base shadow hover:bg-gray-700 transition">Addition</button>
          <button class="w-full mb-2 py-3 rounded-lg bg-green-600 text-white font-bold text-base shadow hover:bg-green-700 transition">Paiement</button>
          <button class="w-full py-3 rounded-lg bg-red-600 text-white font-bold text-base shadow hover:bg-red-700 transition">Annuler</button>
        </div>
        <div @click="showModal = false" class="fixed inset-0" style="z-index:-1;"></div>
      </div>
    </div>

    <!-- Pavé numérique -->
    <div x-show="selectedIndex!==null" x-transition class="bg-white rounded-2xl shadow p-4">
      <div class="grid grid-cols-4 gap-2">
        <template x-for="btn in touches" :key="btn.label">
          <button @click="handleKey(btn.action)"
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
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 xl:grid-cols-7 gap-2 flex-1 overflow-y-auto pr-2">
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
</div>

{{-- Alpine.js + JS --}}
@php
try {
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
    echo '<div style="color:red;font-weight:bold">Erreur PHP: '.e($e->getMessage()).'</div>';
}
@endphp
<script>
function posApp() {
  return {
    showModal: false,
    showNavMenu: false,
    produits: @json($produitsArray),
    panier: @json($produitsPanier),
    search: '',
    selectedIndex: null,
    showOptions: false,
    currentCat: null,
    client_id: '{{ $client_id }}',
    serveuse_id: '{{ $serveuse_id }}',
    notification: '',
    notificationTimeout: null,
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
          this.showNotification('Client enregistré !');
        } else {
          this.showNotification(data.error || 'Erreur lors de la sélection du client', true);
        }
      })
      .catch(() => this.showNotification('Erreur de connexion', true));
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
          this.showNotification('Serveuse enregistrée !');
        } else {
          this.showNotification(data.error || 'Erreur lors de la sélection de la serveuse', true);
        }
      })
      .catch(() => this.showNotification('Erreur de connexion', true));
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
    // Ajout d'un computed pour filtrer les produits à afficher dans le panier
    get panierAffiche() {
      return this.panier.filter(item => item.qte !== null && item.qte >= 0);
    },
    activeCatClass: 'px-4 py-2 rounded-full bg-blue-600 text-white text-sm font-semibold shadow',
    inactiveCatClass: 'px-4 py-2 rounded-full bg-gray-100 hover:bg-blue-100 text-sm font-semibold shadow',
  }
}
</script>

<!-- Notification (ajouté sous le header panier) -->
<template x-if="notification">
  <div x-text="notification" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-6 py-2 rounded shadow-lg z-50 text-lg font-bold animate-bounce"></div>
</template>

</x-app-layout>
