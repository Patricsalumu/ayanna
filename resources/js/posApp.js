// Alpine.js logic extrait de catalogue.blade.php
// Les variables PHP doivent être passées via le Blade dans le script global (voir instructions plus bas)

function posApp() {
  return {
    showModal: false,
    showNavMenu: false,
    showPaiement: false,
    produits: window.PRODUITS_ARRAY,
    panier: window.PANIER_ARRAY,
    search: '',
    selectedIndex: null,
    showOptions: false,
    currentCat: null,
    client_id: window.CLIENT_ID || '',
    serveuse_id: window.SERVEUSE_ID || '',
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
      {label:'1',action:'1',class:'bg-gray-100', disabledEnPaiement: false},
      {label:'2',action:'2',class:'bg-gray-100', disabledEnPaiement: false},
      {label:'3',action:'3',class:'bg-gray-100', disabledEnPaiement: false},
      {label:'Qté',action:'qte',class:'bg-blue-100', disabledEnPaiement: true},
      {label:'4',action:'4',class:'bg-gray-100', disabledEnPaiement: false},
      {label:'5',action:'5',class:'bg-gray-100', disabledEnPaiement: false},
      {label:'6',action:'6',class:'bg-gray-100', disabledEnPaiement: false},
      {label:'%',action:'remise',class:'bg-yellow-100', disabledEnPaiement: true},
      {label:'7',action:'7',class:'bg-gray-100', disabledEnPaiement: false},
      {label:'8',action:'8',class:'bg-gray-100', disabledEnPaiement: false},
      {label:'9',action:'9',class:'bg-gray-100', disabledEnPaiement: false},
      {label:'C',action:'C',class:'bg-indigo-100', disabledEnPaiement: false},
      {label:'+',action:'+',class:'bg-pink-100', disabledEnPaiement: true},
      {label:'0',action:'0',class:'bg-gray-100', disabledEnPaiement: false},
      {label:'-',action:'-',class:'bg-gray-100', disabledEnPaiement: true},
      {label:'x',action:'x',class:'bg-red-100', disabledEnPaiement: false},
    ],
    activeCatClass: 'px-4 py-2 rounded-full bg-blue-600 text-white text-sm font-semibold shadow ring-2 ring-blue-300 transition',
    inactiveCatClass: 'px-4 py-2 rounded-full bg-gray-100 hover:bg-blue-100 text-sm font-semibold shadow text-blue-600 transition',
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
      // Affiche uniquement les produits à qte > 0
      return this.panier.filter(item => item.qte > 0);
    },
    inqte(prod_id) {
      // Affiche le badge uniquement si la quantité > 0
      const i = this.panier.find(i => i.id === prod_id);
      return i && i.qte > 0 ? i.qte : null;
    },
    selectCat(id){
      this.currentCat = id;
    },
    toggleOptions(){
      this.showOptions = !this.showOptions;
    },
    ajouterProduit(prod){
      const idx = this.panier.findIndex(i => i.id === prod.id);
      if (idx >= 0) this.panier[idx].qte++;
      else this.panier.push({ ...prod, qte: 1 });
      fetch(`/vente/panier/ajouter/${prod.id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            quantite: 1,
            table_id: window.TABLE_COURANTE,
            point_de_vente_id: window.POINT_DE_VENTE_ID
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          this.panier = data.panier;
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
      fetch(window.SET_CLIENT_URL, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': window.CSRF_TOKEN,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          client_id: id ? Number(id) : null,
          table_id: window.TABLE_COURANTE,
          point_de_vente_id: window.POINT_DE_VENTE_ID
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
      fetch(window.SET_SERVEUSE_URL, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': window.CSRF_TOKEN,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          serveuse_id: id ? Number(id) : null,
          table_id: window.TABLE_COURANTE,
          point_de_vente_id: window.POINT_DE_VENTE_ID
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
    // Correction : méthode pour sélectionner un item du panier et afficher le pavé numérique
    selectItem(idx) {
      this.selectedIndex = idx;
    },
    // Correction : méthode pour passer en mode paiement
    openPaiement() {
      this.mode = 'paiement';
      this.selectedIndex = null;
    },
    // Correction : méthode pour libérer la table
    libererTable() {
      fetch('/panier/liberer', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': window.CSRF_TOKEN,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          table_id: window.TABLE_COURANTE,
          point_de_vente_id: window.POINT_DE_VENTE_ID
        })
      })
      .then(res => res.json())
      .then(data => {
        if(data.success && data.redirect_url) {
          window.location.href = data.redirect_url;
        } else if(data.success) {
          window.location.reload();
        } else {
          alert(data.error || 'Erreur lors de la libération de la table');
        }
      })
      .catch(() => alert('Erreur de connexion avec le serveur'));
    },
    // --- Actions pavé numérique rétablies ---
    handleKey(action) {
      if(this.selectedIndex===null) return;
      const item=this.panier[this.selectedIndex];
      if(!item) return;
      let oldQte = item.qte;
      if(!isNaN(action)){
        // Si on clique un chiffre alors que la qte est à 1, on demande confirmation de suppression
        if(item.qte === 1) {
          if(confirm('Supprimer ce produit du panier ?')) {
            fetch(`/panier/supprimer-produit/${item.id}`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                table_id: window.TABLE_COURANTE,
                point_de_vente_id: window.POINT_DE_VENTE_ID
              })
            })
            .then(res => res.json())
            .then(data => {
              if(data.success) {
                this.panier = data.panier ? data.panier.filter(p => p.qte > 0) : [];
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
          }
          return;
        } else {
          item.qte = parseInt(`${item.qte}${action}`.slice(0,3));
        }
      } else if(action==='x'){
        // Supprime le dernier chiffre, si <=1 alors reset à 1
        if(item.qte > 1) {
          let qteStr = item.qte.toString();
          item.qte = parseInt(qteStr.slice(0, -1)) || 1;
        } else if(item.qte === 1) {
          if(confirm('Supprimer ce produit du panier ?')) {
            fetch(`/panier/supprimer-produit/${item.id}`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                table_id: window.TABLE_COURANTE,
                point_de_vente_id: window.POINT_DE_VENTE_ID
              })
            })
            .then(res => res.json())
            .then(data => {
              if(data.success) {
                this.panier = data.panier ? data.panier.filter(p => p.qte > 0) : [];
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
          }
          return;
        }
      } else if(action==='C') {
        if(item.qte > 1) {
          item.qte = 1;
        } else if(item.qte === 1) {
          if(confirm('Supprimer ce produit du panier ?')) {
            fetch(`/panier/supprimer-produit/${item.id}`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                table_id: window.TABLE_COURANTE,
                point_de_vente_id: window.POINT_DE_VENTE_ID
              })
            })
            .then(res => res.json())
            .then(data => {
              if(data.success) {
                this.panier = data.panier ? data.panier.filter(p => p.qte > 0) : [];
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
          }
          return;
        }
      } else if(action==='+') {
        item.qte++;
      } else if(action==='-') {
        if(item.qte === 1) {
          if(confirm('Supprimer ce produit du panier ?')) {
            fetch(`/panier/supprimer-produit/${item.id}`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                table_id: window.TABLE_COURANTE,
                point_de_vente_id: window.POINT_DE_VENTE_ID
              })
            })
            .then(res => res.json())
            .then(data => {
              if(data.success) {
                this.panier = data.panier ? data.panier.filter(p => p.qte > 0) : [];
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
          }
          return;
        } else if(item.qte > 1) {
          item.qte--;
        }
      }
      // Appel AJAX pour MAJ la base si la quantité a changé
      if(item.qte !== oldQte) {
        fetch(`/panier/modifier-produit/${item.id}`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            quantite: item.qte,
            table_id: window.TABLE_COURANTE,
            point_de_vente_id: window.POINT_DE_VENTE_ID
          })
        })
        .then(res => res.json())
        .then(data => {
          if(data.success) {
            this.panier = data.panier ? data.panier.filter(p => p.qte > 0) : [];
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
    ajouterChiffre(valeur) {
      if(this.mode !== 'paiement') return;
      if(valeur === 'C') {
        this.paiement.montantRecu = 0;
        this.paiement.monnaie = 0;
        return;
      }
      if(valeur === 'x') {
        this.paiement.montantRecu = Math.floor(this.paiement.montantRecu / 10);
        this.paiement.monnaie = this.paiement.montantRecu - this.total;
        return;
      }
      if(!isNaN(valeur)) {
        this.paiement.montantRecu = parseInt(this.paiement.montantRecu.toString() + valeur.toString());
        this.paiement.monnaie = this.paiement.montantRecu - this.total;
      }
    },
    validerPaiement() {
      // Exemple de logique de validation du paiement
      if (this.paiement.montantRecu < this.total) {
        alert('Le montant reçu est insuffisant.');
        return;
      }
      // Appel AJAX pour valider le paiement côté serveur
      fetch('/vente/valider', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': window.CSRF_TOKEN,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          montant_recu: this.paiement.montantRecu,
          monnaie: this.paiement.monnaie,
          mode_paiement: this.paiement.modePaiement,
          client_id: this.paiement.client_id,
          serveuse_id: this.paiement.serveuse_id,
          table_id: window.TABLE_COURANTE,
          point_de_vente_id: window.POINT_DE_VENTE_ID,
          panier_id: (this.panier && this.panier.length && this.panier[0].panier_id) ? this.panier[0].panier_id : (window.PANIER_ID || null)
        })
      })
      .then(res => res.json())
      .then(data => {
        if(data.notification) {
          alert(data.notification);
        }
        if(data.success && data.redirect_url) {
          window.location.href = data.redirect_url;
        } else if(data.success && data.nouveau_panier_id) {
          alert('Paiement validé ! Nouveau panier prêt.');
          // Recharge la page avec le nouvel ID de panier/table (ou reload pour rafraîchir l'état)
          window.location.reload();
        } else if(data.success) {
          alert('Paiement validé !');
          this.mode = 'commande';
          this.paiement.montantRecu = 0;
          this.paiement.monnaie = 0;
        } else {
          alert(data.error || 'Erreur lors du paiement');
        }
      })
      .catch(() => alert('Erreur de connexion avec le serveur'));
    },
    formatMoney(val) {
      if (typeof val !== 'number') val = parseFloat(val) || 0;
      return val.toLocaleString('fr-FR', { minimumFractionDigits: 0 });
    },
  }
}
// Rendre la fonction accessible globalement pour Alpine.js
window.posApp = posApp;
