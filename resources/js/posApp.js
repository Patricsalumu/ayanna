// Alpine.js logic extrait de catalogue.blade.php
// Les variables PHP doivent être passées via le Blade dans le script global (voir instructions plus bas)

export function posApp() {
  return {
    showModal: false,
    bonCommandeEnCours: false,
    bonCommandePrintEnCours: false,
    showNavMenu: false,
    showPaiement: false,
    produits: (() => {
      const cached = localStorage.getItem('ayanna_catalogue_produits');
      if (cached) {
        try {
          const parsed = JSON.parse(cached);
          if (Array.isArray(parsed) && parsed.length) {
            return parsed;
          }
        } catch (e) {}
      }
      return window.PRODUITS_ARRAY || [];
    })(),
    panier: (() => {
      const key = `ayanna_panier_${window.TABLE_COURANTE || 'no-table'}`;
      const cached = localStorage.getItem(key);
      if (cached) {
        try {
          const parsed = JSON.parse(cached);
          if (Array.isArray(parsed)) {
            return parsed;
          }
        } catch (e) {}
      }
      return window.PANIER_ARRAY || [];
    })(),
    search: window.INITIAL_SEARCH || '',
    selectedIndex: null,
    showOptions: false,
    currentCat: (() => {
      if (window.INITIAL_CATEGORY !== undefined && window.INITIAL_CATEGORY !== null && window.INITIAL_CATEGORY !== '') {
        return Number(window.INITIAL_CATEGORY);
      }

      const categories = (window.PRODUITS_ARRAY || [])
        .map(p => Number(p?.categorie_id))
        .filter(value => Number.isFinite(value));

      return categories.length ? categories[0] : null;
    })(),
    userRole: window.USER_ROLE || '',
    canAddProducts: window.CAN_ADD_PRODUCTS !== false,
    canApplyDiscount: window.CAN_APPLY_DISCOUNT === true,
    quantityRestrictedRoles: ['comptoiriste', 'serveuse'],
    client_id: window.CLIENT_ID || '',
    serveuse_id: window.SERVEUSE_ID || '',
    mode_paiement_id: '',
    mode_paiement_nom: '',
    montant_recu: '',
    renduMonnaie: '',
    mode: 'commande',
    remise: 0,
    paiement: {
      montantRecu: 0,
      monnaie: 0,
      modePaiement: 'espèces',
      client_id: window.CLIENT_ID || '',
      serveuse_id: window.SERVEUSE_ID || '',
    },
    touches: [
      {label:'+',action:'+',class:'bg-pink-100', disabledEnPaiement: false},
      {label:'-',action:'-',class:'bg-pink-100', disabledEnPaiement: false},
      {label:'x',action:'x',class:'bg-red-100', disabledEnPaiement: false},
      {label:'C',action:'C',class:'bg-indigo-100', disabledEnPaiement: false},
    ],
    activeCatClass: 'px-4 py-2 rounded-full bg-blue-600 text-white text-sm font-semibold shadow ring-2 ring-blue-300 transition',
    inactiveCatClass: 'px-4 py-2 rounded-full bg-gray-100 hover:bg-blue-100 text-sm font-semibold shadow text-blue-600 transition',
    
    // Fonction pour obtenir la couleur d'une catégorie
    getCategoryColor(categoryId) {
      if (window.getCategoryColor) {
        return window.getCategoryColor(categoryId);
      }
      // Couleurs par défaut si la fonction globale n'est pas disponible
      const colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-yellow-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500', 'bg-orange-500', 'bg-cyan-500'];
      return colors[(categoryId || 0) % colors.length];
    },
    truncateProductName(name, maxLength = 25) {
      const value = typeof name === 'string' ? name.trim() : '';
      if (!value) {
        return '';
      }
      return value.length > maxLength ? `${value.slice(0, maxLength)}…` : value;
    },
    getLocalCacheKey(prefix) {
      return `${prefix}_${window.POINT_DE_VENTE_ID || 'global'}_${window.TABLE_COURANTE || 'no-table'}`;
    },
    persistCachedCart() {
      const key = this.getLocalCacheKey('ayanna_panier');
      localStorage.setItem(key, JSON.stringify(this.panier || []));
      localStorage.setItem('ayanna_catalogue_produits', JSON.stringify(this.produits || []));
    },
    async refreshCatalogueFromServer() {
      const refreshUrl = new URL(window.location.href);
      refreshUrl.searchParams.set('ajax', '1');

      try {
        const response = await fetch(refreshUrl.toString(), {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json().catch(() => null);
        if (data && Array.isArray(data.produits)) {
          this.produits = data.produits;
          localStorage.setItem('ayanna_catalogue_produits', JSON.stringify(this.produits));
        }

        if (data && Array.isArray(data.tables)) {
          localStorage.setItem('ayanna_tables', JSON.stringify(data.tables));
        }

        if (data && Array.isArray(data.serveuses)) {
          localStorage.setItem('ayanna_serveuses', JSON.stringify(data.serveuses));
        }

        if (!data || !Array.isArray(data.produits)) {
          window.location.reload();
        }
      } catch (error) {
        console.warn('Rafraîchissement catalogue impossible:', error);
        window.location.reload();
      }
    },
    async logoutServeuse() {
      try {
        const response = await fetch('/logout', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({ serveuse_logout: true }),
          credentials: 'same-origin',
        });

        if (response.redirected || response.ok) {
          window.location.href = '/serveuse-login';
          return;
        }
      } catch (error) {
        console.warn('Déconnexion serveuse impossible via fetch:', error);
      }

      window.location.href = '/serveuse-login';
    },
    
    get totalHt(){
      return this.panier.filter(item => item.qte > 0).reduce((sum,item) => sum + item.qte * item.prix, 0);
    },
    get totalRemise(){
      return Math.max(0, Number(this.remise) || 0);
    },
    get totalTva(){
      return 0;
    },
    get total(){
      return Math.max(0, this.totalHt - this.totalRemise);
    },
    get filteredProduits(){
      const query = (this.search || '').trim().toLowerCase();

      return this.produits.filter(p => {
        const prodCat = p?.categorie_id !== undefined && p?.categorie_id !== null ? Number(p.categorie_id) : null;
        const activeCat = this.currentCat !== undefined && this.currentCat !== null && this.currentCat !== '' ? Number(this.currentCat) : null;
        const nameMatches = (p.nom && p.nom.toLowerCase().includes(query));

        if (query) {
          return nameMatches;
        }

        if (activeCat === null) {
          return true;
        }

        return prodCat !== null && prodCat === activeCat;
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
    isQuantityRestricted() {
      return this.quantityRestrictedRoles.includes(String(this.userRole || '').trim().toLowerCase());
    },
    isRestrictedQuantityAction(action) {
      return this.isQuantityRestricted() && ['C', 'x', '-'].includes(action);
    },
    isKeyDisabled(btn) {
      return (this.mode === 'paiement' && btn.disabledEnPaiement) || (this.mode !== 'paiement' && !this.canAddProducts);
    },
    async demanderAutorisationAdmin(actionLabel) {
      if (!this.isQuantityRestricted()) {
        return null;
      }

      const password = window.prompt(`Mot de passe administrateur requis pour ${actionLabel}.`);
      if (password === null || password === '') {
        return null;
      }

      try {
        const response = await fetch('/panier/valider-admin', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ password_admin: password })
        });
        const data = await response.json();
        if (!data.success) {
          alert(data.error || 'Mot de passe administrateur invalide.');
          return null;
        }
        return password;
      } catch (error) {
        console.error('Erreur validation admin:', error);
        alert('Erreur de connexion avec le serveur.');
        return null;
      }
    },
    async syncPanierToServer() {
      if (!window.TABLE_COURANTE || !window.POINT_DE_VENTE_ID) {
        return { success: true, skipped: true };
      }

      const payload = (this.panier || []).filter(item => Number(item.qte || 0) > 0).map(item => ({
        id: item.id,
        qte: Number(item.qte || 0),
        prix: Number(item.prix || 0),
        nom: item.nom || '',
      }));

      if (!payload.length) {
        return { success: true, skipped: true };
      }

      const response = await fetch('/vente/panier/sync', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': window.CSRF_TOKEN,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          table_id: window.TABLE_COURANTE,
          point_de_vente_id: window.POINT_DE_VENTE_ID,
          panier: payload,
        })
      });

      const data = await response.json();
      if (!response.ok || !data.success) {
        throw new Error(data.error || 'Impossible de sauvegarder le panier');
      }

      if (data.panier_id) {
        window.PANIER_ID = data.panier_id;
      }

      return data;
    },
    selectCat(id) {
      this.currentCat = Number(id);
    },
    toggleOptions(){
      this.showOptions = !this.showOptions;
    },
    ajouterProduit(prod){
      const idx = this.panier.findIndex(i => i.id === prod.id);
      if (idx >= 0) this.panier[idx].qte++;
      else this.panier.push({ ...prod, qte: 1 });

      this.persistCachedCart();
      return;
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
      this.paiement.montantRecu = this.total;
      this.paiement.monnaie = 0;
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
    async handleKey(action) {
      if (!this.canAddProducts) return;
      if(this.selectedIndex===null) return;
      const item=this.panier[this.selectedIndex];
      if(!item) return;

      const passwordAdmin = this.isRestrictedQuantityAction(action)
        ? await this.demanderAutorisationAdmin('réduire ou supprimer un produit')
        : null;

      if(this.isRestrictedQuantityAction(action) && !passwordAdmin) return;

      let oldQte = item.qte;
      if(!isNaN(action)){
        // Saisie d'un chiffre
        const chiffre = parseInt(action);
        if(item.qte === 1) {
          if(chiffre === 1) {
            // 1 + 1 => 11
            item.qte = 11;
          } else {
            // 1 + [2-9] => remplace par le chiffre
            item.qte = chiffre;
          }
        } else {
          // [2-9] ou plusieurs chiffres + [1-9] => concatène
          item.qte = parseInt(`${item.qte}${chiffre}`.slice(0,3));
        }
      } else if(action==='x'){
        // Supprime le dernier chiffre, si <=1 alors reset à 1 ou demande suppression
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
                point_de_vente_id: window.POINT_DE_VENTE_ID,
                password_admin: passwordAdmin
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
                point_de_vente_id: window.POINT_DE_VENTE_ID,
                password_admin: passwordAdmin
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
                point_de_vente_id: window.POINT_DE_VENTE_ID,
                password_admin: passwordAdmin
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
      // Sauvegarde locale seulement : la commande finale sera enregistrée en base
      if(item.qte !== oldQte) {
        if(this.isQuantityRestricted() && item.qte < oldQte && !passwordAdmin) {
          item.qte = oldQte;
          return;
        }
        this.panier = this.panier.filter(p => p.qte > 0);
        this.persistCachedCart();
        if(this.selectedIndex !== null && this.selectedIndex >= this.panier.length) {
          this.selectedIndex = this.panier.length > 0 ? this.panier.length-1 : null;
        }
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
      if (this.paiement.montantRecu <= 0) {
        alert('Veuillez saisir un montant reçu supérieur à zéro.');
        return;
      }

      this.syncPanierToServer()
        .then(() => fetch('/vente/valider', {
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
            remise: this.canApplyDiscount ? this.remise : 0,
            table_id: window.TABLE_COURANTE,
            point_de_vente_id: window.POINT_DE_VENTE_ID,
            panier_id: (this.panier && this.panier.length && this.panier[0].panier_id) ? this.panier[0].panier_id : (window.PANIER_ID || null)
          })
        }))
        .then(res => res.json())
        .then(data => {
          if(data.notification) {
            alert(data.notification);
          }
          if(data.success && data.redirect_url) {
            window.location.href = data.redirect_url;
          } else if(data.success && data.nouveau_panier_id) {
            alert('Paiement validé ! Nouveau panier prêt.');
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
    async validerEtImprimer() {
      if (this.paiement.montantRecu <= 0) {
        alert('Veuillez saisir un montant reçu supérieur à zéro.');
        return;
      }
      try {
        await this.syncPanierToServer();
        const response = await fetch('/vente/valider', {
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
            remise: this.canApplyDiscount ? this.remise : 0,
            table_id: window.TABLE_COURANTE,
            point_de_vente_id: window.POINT_DE_VENTE_ID,
            panier_id: (this.panier && this.panier.length && this.panier[0].panier_id) ? this.panier[0].panier_id : (window.PANIER_ID || null)
          })
        });
        const data = await response.json();
        if(data.notification) {
          alert(data.notification);
        }
        if(data.success) {
          this.printAddition('paiement');
          if(data.redirect_url) {
            setTimeout(() => { window.location.href = data.redirect_url; }, 1000);
          } else {
            setTimeout(() => { window.location.reload(); }, 1000);
          }
        } else {
          alert(data.error || 'Erreur lors du paiement');
        }
      } catch (e) {
        alert('Erreur de connexion avec le serveur');
      }
    },
    async printAddition(type = 'proforma') {
      const tableId = window.TABLE_COURANTE;
      const pointDeVenteId = window.POINT_DE_VENTE_ID;
      const panierId = window.PANIER_ID || null;
      let panier = Array.isArray(this.panier) ? this.panier.filter(item => Number(item.qte || 0) > 0) : [];

      try {
        const payloadExists = panier.length > 0;
        if (payloadExists) {
          await this.syncPanierToServer();
        }
      } catch (error) {
        console.warn('Synchronisation panier avant impression impossible:', error);
      }

      const query = new URLSearchParams();
      if (window.PANIER_ID) {
        query.set('panier_id', String(window.PANIER_ID));
      } else if (panierId) {
        query.set('panier_id', String(panierId));
      } else if (tableId) {
        query.set('table_id', String(tableId));
      }
      if (pointDeVenteId) {
        query.set('point_de_vente_id', String(pointDeVenteId));
      }

      if (query.toString()) {
        try {
          const response = await fetch(`/vente/panier/base?${query.toString()}`);
          const data = await response.json();
          if (data && Array.isArray(data.panier)) {
            panier = data.panier.filter(item => Number(item.qte || 0) > 0);
            if (data.panier_id) {
              window.PANIER_ID = data.panier_id;
            }
          }
        } catch (error) {
          console.warn('Impossible de récupérer le panier enregistré en base pour l’impression:', error);
          panier = [];
        }
      }

      if ((!panier || !panier.length) && Array.isArray(this.panier) && this.panier.length) {
        panier = this.panier.filter(item => Number(item.qte || 0) > 0);
      }

      const table = window.TABLE_COURANTE_LABEL || '';
      const entreprise = window.ENTREPRISE || {};
      const client = this.paiement.client_id ? (window.CLIENTS?.find?.(c => c.id == this.paiement.client_id) ?? null) : null;
      const serveuse = this.paiement.serveuse_id ? (window.SERVEUSES?.find?.(s => s.id == this.paiement.serveuse_id) ?? null) : null;
      const activePanierId = window.PANIER_ID || panierId;

      const baseTotalHt = panier.reduce((sum, item) => sum + (Number(item.qte || 0) * Number(item.prix || 0)), 0);
      const baseTotalRemise = Number(this.totalRemise || 0);
      const baseTotal = Math.max(0, baseTotalHt - baseTotalRemise);
      let total = 0;
      let now = new Date();
      let dateStr = now.toLocaleDateString('fr-FR');
      let heureStr = now.toLocaleTimeString('fr-FR');
      let html = `<div style='width:75mm;padding:0;margin:0;font-family:monospace;background:#fff;color:#111;box-sizing:border-box;font-weight:bold;'>`;
      if(type === 'proforma') {
        html += `<div style='text-align:center;font-size:20px;font-weight:bold;color:#111;margin-bottom:6px;letter-spacing:0.5px;'>PRE-FACTURE</div>`;
      } else {
        html += `<div style='text-align:center;font-size:20px;font-weight:bold;color:#111;margin-bottom:6px;letter-spacing:0.5px;'>REÇU DE PAIEMENT</div>`;
      }
      if(entreprise.logo) {
        html += `<div style='text-align:center;'><img src='${window.location.origin}/storage/${entreprise.logo}' style='max-width:56px;max-height:56px;margin-bottom:6px;display:block;margin-left:auto;margin-right:auto;'/></div>`;
      }
      html += `<div style='text-align:center;font-weight:bold;font-size:20px;color:#111;'>${entreprise.nom ?? ''}</div>`;
      if(entreprise.numero_entreprise) html += `<div style='text-align:center;font-size:14px;color:#111;'>N° Entreprise : ${entreprise.numero_entreprise}</div>`;
      if(entreprise.email) html += `<div style='text-align:center;font-size:14px;color:#111;'>${entreprise.email}</div>`;
      if(entreprise.telephone) html += `<div style='text-align:center;font-size:14px;color:#111;'>${entreprise.telephone}</div>`;
      if(entreprise.adresse) html += `<div style='text-align:center;font-size:14px;color:#111;'>${entreprise.adresse}</div>`;
      html += `<div style='border-top:1px solid #111;margin:8px 0;'></div>`;
      html += `<div style='font-size:15px;color:#111;font-weight:bold;'>Client : <b>${client?.nom ?? '-'}</b></div>`;
      html += `<div style='font-size:15px;color:#111;font-weight:bold;'>Serveuse : <b>${serveuse?.name ?? '-'}</b></div>`;
      html += `<div style='font-size:15px;color:#111;font-weight:bold;'>Table : <b>${table}</b> | Panier n° <b>${activePanierId ?? '-'}</b></div>`;
      if(type === 'paiement') {
        html += `<div style='font-size:15px;color:#111;font-weight:bold;'>Mode de paiement : <b>${this.paiement.modePaiement === 'espèces' ? 'Espèces' : (this.paiement.modePaiement === 'mobile_money' ? 'Mobile Money' : (this.paiement.modePaiement === 'compte_client' ? 'Compte Client' : this.paiement.modePaiement))}</b></div>`;
      }
      html += `<div style='border-top:1px solid #111;margin:8px 0;'></div>`;
      html += `<table style='width:100%;font-size:15px;margin:0 auto;border-collapse:collapse;color:#111;font-weight:bold;'><thead><tr><th style='text-align:left;border-bottom:1px solid #111;padding:2px 0;font-weight:bold;'>Produit</th><th style='border-bottom:1px solid #111;padding:2px 0;font-weight:bold;'>Qté</th><th style='text-align:right;border-bottom:1px solid #111;padding:2px 0;font-weight:bold;'>Prix</th><th style='text-align:right;border-bottom:1px solid #111;padding:2px 0;font-weight:bold;'>Total</th></tr></thead><tbody>`;
      panier.forEach(item => {
        const qte = Number(item.qte || 0);
        const prix = Number(item.prix || 0);
        const lineTotal = qte * prix;
        total += lineTotal;
        html += `<tr><td style='word-break:break-all;padding:2px 0;border-bottom:1px solid rgba(17,17,17,0.4);color:#111;font-weight:bold;'>${item.nom}</td><td style='text-align:center;padding:2px 0;border-bottom:1px solid rgba(17,17,17,0.4);color:#111;font-weight:bold;'>${qte}</td><td style='text-align:right;padding:2px 0;border-bottom:1px solid rgba(17,17,17,0.4);color:#111;font-weight:bold;'>${this.formatMoney(prix)}</td><td style='text-align:right;padding:2px 0;border-bottom:1px solid rgba(17,17,17,0.4);color:#111;font-weight:bold;'>${this.formatMoney(lineTotal)}</td></tr>`;
      });
      html += `</tbody></table>`;
      html += `<div style='border-top:1px solid #111;margin:8px 0;'></div>`;
      html += `<div style='text-align:right;font-size:16px;color:#111;font-weight:bold;'>Sous-total : ${this.formatMoney(baseTotalHt)}</div>`;
      html += `<div style='text-align:right;font-size:16px;color:#111;font-weight:bold;'>Remise : ${this.formatMoney(baseTotalRemise)}</div>`;
      html += `<div style='text-align:right;font-size:20px;font-weight:bold;color:#111;'>Net à payer : ${this.formatMoney(baseTotal)}</div>`;
      if (this.showFEquivalent(baseTotal)) {
        html += `<div style='text-align:right;font-size:15px;color:#111;font-weight:bold;'>Équivalent F : ${this.formatFEquivalent(baseTotal)}</div>`;
      }
      html += `<div style='text-align:center;font-size:15px;margin-top:12px;color:#111;font-weight:bold;'>Merci pour votre visite !</div>`;
      html += `<div style='text-align:center;font-size:13px;margin-top:10px;color:#111;font-weight:bold;'>Généré par Ayanna &copy; | ${dateStr} ${heureStr}</div>`;
      html += `</div>`;
      document.getElementById('ticket-addition').innerHTML = html;
      const printWindow = window.open('', '', 'width=900,height=800');
      printWindow.document.write('<html><head><title>Préfacture</title>');
      printWindow.document.write('<style>html,body{margin:0;padding:0;background:#fff;color:#111;}body{display:flex;justify-content:center;}@media print{body{width:75mm!important;background:#fff;color:#111;}}</style>');
      printWindow.document.write('</head><body >');
      printWindow.document.write(html);
      printWindow.document.write('</body></html>');
      printWindow.document.close();
      printWindow.focus();
      setTimeout(()=>{printWindow.print(); printWindow.close();}, 800);
      if (panier.length && activePanierId) {
        fetch(`/panier/impression/${activePanierId}`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            total: baseTotal,
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
    formatMoney(val) {
      if (typeof val !== 'number') val = parseFloat(val) || 0;
      const symbol = window.ENTREPRISE?.devise || '$';
      return `${val.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${symbol}`;
    },
    showFEquivalent(val) {
      const devise = window.ENTREPRISE?.devise || '$';
      const taux = Number(window.ENTREPRISE?.taux || 0);
      return devise === '$' && Number.isFinite(taux) && taux > 0 && Number(val) > 0;
    },
    formatFEquivalent(val) {
      if (typeof val !== 'number') val = parseFloat(val) || 0;
      const taux = Number(window.ENTREPRISE?.taux || 0);
      const montantF = val * taux;
      return `${montantF.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 })} F`;
    },
    async printBonCommande(bonId) {
      if (this.bonCommandePrintEnCours) {
        return;
      }

      this.bonCommandePrintEnCours = true;

      try {
        const response = await fetch(`/bon-commande/${bonId}/print`);
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        const html = await response.text();

        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.right = '-9999px';
        iframe.style.bottom = '-9999px';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);

        const printWindow = iframe.contentWindow;
        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
          try {
            printWindow.print();
          } catch (printErr) {
            console.error('Erreur lors de print() :', printErr);
          }
        }, 100);

        setTimeout(() => {
          try {
            iframe.remove();
          } catch (e) {}
        }, 600);

        const redirectToPlanVente = () => {
          const entrepriseId = window.ENTREPRISE_ID || window.ENTREPRISE?.id || '';
          const pointDeVenteId = window.POINT_DE_VENTE_ID || '';
          const salleId = window.SALLE_ID || '';
          if (entrepriseId && salleId && pointDeVenteId) {
            window.location.href = `/entreprises/${encodeURIComponent(entrepriseId)}/salles/${encodeURIComponent(salleId)}/plan-vente?point_de_vente_id=${encodeURIComponent(pointDeVenteId)}`;
          } else if (pointDeVenteId) {
            window.location.href = `/vente/catalogue/${encodeURIComponent(pointDeVenteId)}`;
          } else {
            window.location.reload();
          }
        };

        setTimeout(redirectToPlanVente, 500);
      } catch (err) {
        console.error('Erreur impression bon commande :', err);
        alert('❌ Erreur d\'impression du bon de commande.\n\nVérifiez votre connexion internet ou réessayez.');
      } finally {
        this.bonCommandePrintEnCours = false;
      }
    },
    genererBonCommande() {
      if (this.bonCommandeEnCours || this.bonCommandePrintEnCours) {
        return;
      }

      this.bonCommandeEnCours = true;

      // Vérifier qu'une serveuse est sélectionnée
      if (!this.paiement.serveuse_id) {
        alert('❌ Veuillez sélectionner une serveuse avant de générer un bon de commande.');
        this.showModal = false;
        this.bonCommandeEnCours = false;
        return;
      }

      // Récupérer l'ID du panier
      const panierId = (this.panier && this.panier.length && this.panier[0].panier_id) ? this.panier[0].panier_id : window.PANIER_ID;
      if (!panierId) {
        alert('❌ Impossible de générer un bon : aucun panier n\'est actif.');
        this.bonCommandeEnCours = false;
        return;
      }

      console.log('🔵 Envoi bon commande:', { panierId, serveuse_id: this.paiement.serveuse_id });

      this.syncPanierToServer()
        .then(() => fetch('/bon-commande/create', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            panier_id: panierId,
            serveuse_id: this.paiement.serveuse_id
          })
        }))
        .then(res => {
          console.log('🔵 Réponse reçue:', res.status);
          if (!res.ok) {
            throw new Error(`HTTP ${res.status}`);
          }
          return res.json();
        })
        .then(data => {
          console.log('🔵 Données reçues:', data);
          this.showModal = false;

          if (data.code === 'no_serveuse') {
            alert('❌ ' + data.error);
            return;
          }

          if (data.code === 'no_new_products') {
            alert('⚠️ Aucun nouveau produit à imprimer.\n\nVeuillez ajouter des produits au panier.');
            return;
          }

          if (data.success) {
            this.printBonCommande(data.bon_id);
          } else {
            alert('❌ Erreur : ' + (data.error || 'Impossible de générer le bon'));
            if (data.message) {
              console.error('Erreur détaillée:', data.message);
            }
          }
        })
        .catch(err => {
          console.error('❌ Erreur:', err);
          alert('❌ Erreur de connexion avec le serveur:\n' + err.message + '\n\nVérifiez les logs du serveur pour plus de détails.');
        })
        .finally(() => {
          if (!this.bonCommandePrintEnCours) {
            this.bonCommandeEnCours = false;
          }
        });
    },
  }
}
// Rendre la fonction accessible globalement pour Alpine.js
window.posApp = posApp;
