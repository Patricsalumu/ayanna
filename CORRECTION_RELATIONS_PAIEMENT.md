## ✅ CORRECTION APPLIQUÉE

### Problème identifié :
L'erreur **"Attempt to read property 'entreprise_id' on null"** était causée par le fait que les relations nécessaires n'étaient pas chargées dans la requête Eloquent.

### Correction appliquée :
```php
// AVANT (ligne ~595)
$commande = Commande::with(['panier.produits', 'paiements'])->findOrFail($commandeId);

// APRÈS (correction)
$commande = Commande::with(['panier.produits', 'panier.pointDeVente', 'panier.client', 'paiements'])->findOrFail($commandeId);
```

### Relations ajoutées :
- ✅ `panier.pointDeVente` : Nécessaire pour accéder à `entreprise_id` et `point_de_vente_id`
- ✅ `panier.client` : Nécessaire pour le libellé du paiement dans `entrees_sorties`

### Fichier modifié :
- `app/Http/Controllers/VenteController.php` (méthode `enregistrerPaiement`)

### Test :
La correction a été appliquée. Vous pouvez maintenant tester l'enregistrement d'un paiement de créance via l'interface web.

### Ce qui devrait maintenant fonctionner :
1. ✅ Enregistrement du paiement dans la table `paiements`
2. ✅ Génération des écritures comptables via `ComptabiliteService`
3. ✅ Création d'une entrée dans `entrees_sorties` pour le rapport
4. ✅ Apparition dans le rapport journalier section "Paiements créances"
5. ✅ Mise à jour du solde final dans le rapport
