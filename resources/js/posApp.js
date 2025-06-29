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
      client_id: window.CLIENT_ID || '',
      serveuse_id: window.SERVEUSE_ID || '',
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
    
    // Fonction pour obtenir la couleur d'une catégorie
    getCategoryColor(categoryId) {
      if (window.getCategoryColor) {
        return window.getCategoryColor(categoryId);
      }
      // Couleurs par défaut si la fonction globale n'est pas disponible
      const colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-yellow-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500', 'bg-orange-500', 'bg-cyan-500'];
      return colors[(categoryId || 0) % colors.length];
    },
    
    get total(){
      return this.panier.filter(item => item.qte > 0).reduce((a,b)=> a+ b.qte*b.prix,0);
    },
    get filteredProduits(){
      return this.produits.filter(p => {
        return (!this.currentCat || p.categorie_id===this.currentCat)
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
    handleKey(action) {
      if(this.selectedIndex===null) return;
      const item=this.panier[this.selectedIndex];
      if(!item) return;
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
    async validerEtImprimer() {
      // Même logique que validerPaiement mais imprime si succès
      if (this.paiement.montantRecu < this.total) {
        alert('Le montant reçu est insuffisant.');
        return;
      }
      try {
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
    printAddition(type = 'proforma') {
      const panier = this.panier || [];
      const table = window.TABLE_COURANTE_LABEL || '';
      const pointDeVente = window.POINT_DE_VENTE_NOM || '';
      const entreprise = window.ENTREPRISE || {};
      // Infos client/serveuse/table/panier
      const client = this.paiement.client_id ? (window.CLIENTS?.find?.(c => c.id == this.paiement.client_id) ?? null) : null;
      const serveuse = this.paiement.serveuse_id ? (window.SERVEUSES?.find?.(s => s.id == this.paiement.serveuse_id) ?? null) : null;
      const panierId = window.PANIER_ID;
      let total = 0;
      let now = new Date();
      let dateStr = now.toLocaleDateString('fr-FR');
      let heureStr = now.toLocaleTimeString('fr-FR');
      let html = `<div style='width:58mm;padding:0;font-family:monospace;'>`;
      // Type de reçu
      if(type === 'proforma') {
        html += `<div style='text-align:center;font-size:13px;font-weight:bold;color:#888;margin-bottom:2px;'>ADDITION / PROFORMA</div>`;
      } else {
        html += `<div style='text-align:center;font-size:13px;font-weight:bold;color:#222;margin-bottom:2px;'>REÇU DE PAIEMENT</div>`;
      }
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
      if(type === 'paiement') {
        html += `<div style='font-size:11px;'>Mode de paiement : <b>${this.paiement.modePaiement === 'espèces' ? 'Espèces' : (this.paiement.modePaiement === 'mobile_money' ? 'Mobile Money' : (this.paiement.modePaiement === 'compte_client' ? 'Compte Client' : this.paiement.modePaiement))}</b></div>`;
      }
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
      setTimeout(()=>{printWindow.print(); printWindow.close();}, 800);
      // Enregistrement du snapshot d'impression
      if (panier.length && panierId) {
        fetch(`/panier/impression/${panierId}`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
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
    formatMoney(val) {
      if (typeof val !== 'number') val = parseFloat(val) || 0;
      return val.toLocaleString('fr-FR', { minimumFractionDigits: 0 });
    },
  }
}
// Rendre la fonction accessible globalement pour Alpine.js
window.posApp = posApp;
