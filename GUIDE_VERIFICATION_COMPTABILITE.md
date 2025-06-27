# GUIDE DE VÉRIFICATION COMPTABILITÉ

## Étapes de vérification :

### 1. Adapter la configuration de base de données

Ouvrez le fichier `verif_comptabilite_manuelle.php` et modifiez la ligne :
```php
'database' => 'nom_de_votre_base', // Changez ici
```

Remplacez par le nom réel de votre base de données.

### 2. Exécuter le script

Dans PowerShell, allez dans votre dossier projet et exécutez :
```powershell
cd c:\wamp64\www\ayanna
php verif_comptabilite_manuelle.php
```

### 3. Analyser les résultats

Le script vous donnera :
- ✓ = Fonctionnement correct
- ✗ = Problème détecté

### 4. Tests manuels recommandés

1. **Test de vente simple :**
   - Créez une commande avec 1-2 produits
   - Validez avec "especes"
   - Vérifiez que l'écriture apparaît dans le journal

2. **Test de différents modes de paiement :**
   - Testez avec "carte_bancaire"
   - Testez avec "compte_client" (si configuré)
   - Vérifiez que les comptes utilisés sont différents

3. **Test d'équilibre comptable :**
   - Pour chaque vente, vérifiez que Débit = Crédit
   - Vérifiez que le montant du journal = montant de la vente

### 5. Vérification dans l'interface web

1. **Journal comptable :**
   - Menu Comptabilité → Journal
   - Vérifiez l'affichage des montants
   - Triez par date pour voir les dernières écritures

2. **Grand livre :**
   - Menu Comptabilité → Grand livre
   - Sélectionnez un compte (ex: "Caisse")
   - Vérifiez les mouvements

3. **Bilan/Compte de résultat :**
   - Menu Comptabilité → Bilan
   - Menu Comptabilité → Compte de résultat
   - Vérifiez que les totaux sont cohérents

### 6. Points de contrôle essentiels

- [ ] Les ventes génèrent automatiquement des écritures comptables
- [ ] Les montants dans le journal correspondent aux montants des ventes
- [ ] Chaque écriture a ses débits = crédits
- [ ] Les numéros de pièce sont uniques
- [ ] L'isolation par entreprise fonctionne (si multi-entreprise)
- [ ] Les différents modes de paiement utilisent les bons comptes

### 7. En cas de problème

Si vous détectez des anomalies :

1. **Pas d'écritures générées :**
   - Vérifiez que la méthode `valider` du VenteController appelle bien le service comptable
   - Vérifiez les logs Laravel (storage/logs/laravel.log)

2. **Montants incorrects :**
   - Comparez avec le script de vérification
   - Vérifiez les calculs dans ComptabiliteService

3. **Déséquilibre comptable :**
   - Vérifiez la configuration des comptes
   - Vérifiez les règles de débit/crédit

4. **Problèmes d'affichage :**
   - Vérifiez les vues Blade
   - Vérifiez que les colonnes en base correspondent

### 8. Maintenance recommandée

- Exécutez le script de vérification régulièrement
- Surveillez les logs d'erreur
- Vérifiez périodiquement l'équilibre général (total débits = total crédits)
- Sauvegardez régulièrement la base de données
