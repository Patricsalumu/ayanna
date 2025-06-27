<?php
namespace App\Models\Defaults;

use App\Models\PointDeVente;
use App\Models\Salle;
use App\Models\TableResto;
use App\Models\Categorie;
use App\Models\Produit;
use App\Models\ClasseComptable;
use App\Models\Compte;

class PosRestaubarDefaults
{
    public static function initialiserPour($module, $entreprise)
    {
        // 1. INITIALISER LE PLAN COMPTABLE DE BASE
        self::initialiserComptabilite($entreprise);
        
        // 2. Créer un point de vente par défaut
        $pdv = PointDeVente::create([
            'nom' => 'Restaurant',
            'module_id' => $module->id,
            'entreprise_id' => $entreprise->id,
        ]);

        // 3. CONFIGURER LES COMPTES COMPTABLES DU POINT DE VENTE
        self::configurerComptesPointDeVente($pdv, $entreprise);

        // Créer 2 catégories
        $cat1 = Categorie::create(['nom' => 'Bières', 'entreprise_id' => $entreprise->id]);
        $cat3 = Categorie::create(['nom' => 'Sucrés', 'entreprise_id' => $entreprise->id]);
        $cat4 = Categorie::create(['nom' => 'Whisky', 'entreprise_id' => $entreprise->id]);
        $cat2 = Categorie::create(['nom' => 'Cuisine', 'entreprise_id' => $entreprise->id]);

        // Associer les catégories au point de vente (pivot)
        $pdv->categories()->attach([$cat1->id, $cat2->id, $cat3->id, $cat4->id]);

        // Créer quelques produits dans chaque catégorie
        Produit::create([
            'nom' => 'Heineken',
            'description' => 'Bière blonde 33cl',
            'prix_achat' => 1000,
            'prix_vente' => 2000,
            'categorie_id' => $cat1->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Castel lite',
            'description' => 'Bière blonde 33cl',
            'prix_achat' => 2000,
            'prix_vente' => 4000,
            'categorie_id' => $cat1->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Heineken',
            'description' => 'Bière blonde 33cl',
            'prix_achat' => 2000,
            'prix_vente' => 4000,
            'categorie_id' => $cat1->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Simba',
            'description' => 'Bière brasimba 73 cl',
            'prix_achat' =>3500,
            'prix_vente' => 6000,
            'categorie_id' => $cat1->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Tembo',
            'description' => 'Bière brasimba 73cl',
            'prix_achat' => 3500,
            'prix_vente' => 6000,
            'categorie_id' => $cat1->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Castel',
            'description' => 'Bière brasimba 67cl',
            'prix_achat' => 3500,
            'prix_vente' => 6000,
            'categorie_id' => $cat1->id,
            'point_de_vente_id' => $pdv->id,
        ]);
    //categorie cuisine
        Produit::create([
            'nom' => 'Tilapia',
            'description' => 'Plat accompagné',
            'prix_achat' => 10000,
            'prix_vente' => 30000,
            'categorie_id' => $cat2->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Poulet complet',
            'description' => 'Poulet complet ',
            'prix_achat' => 15000,
            'prix_vente' => 40000,
            'categorie_id' => $cat2->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Demi Poulet',
            'description' => 'Plat complet viande grillée',
            'prix_achat' => 8000,
            'prix_vente' => 25000,
            'categorie_id' => $cat2->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Ailes de poulet + Frites',
            'description' => 'Plat complet viande grillée',
            'prix_achat' => 12000,
            'prix_vente' => 25000,
            'categorie_id' => $cat2->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Boulettes sipmle',
            'description' => 'Boulette simple non accompagné',
            'prix_achat' => 7500,
            'prix_vente' => 15000,
            'categorie_id' => $cat2->id,
            'point_de_vente_id' => $pdv->id,
        ]);
       //Categorie sucrés
         Produit::create([
            'nom' => 'Coca cola',
            'description' => 'Plat complet viande grillée',
            'prix_achat' => 1200,
            'prix_vente' => 3000,
            'categorie_id' => $cat3->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Coca cola Gf',
            'description' => 'bouteille grand format',
            'prix_achat' => 1200,
            'prix_vente' => 5000,
            'categorie_id' => $cat3->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Djino',
            'description' => 'bouteille cassable 33cl',
            'prix_achat' => 1200,
            'prix_vente' => 3000,
            'categorie_id' => $cat3->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Jus ceres',
            'description' => 'carton',
            'prix_achat' => 3000,
            'prix_vente' => 6000,
            'categorie_id' => $cat3->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Pepsi',
            'description' => 'bouteille plastique',
            'prix_achat' => 1200,
            'prix_vente' => 3000,
            'categorie_id' => $cat3->id,
            'point_de_vente_id' => $pdv->id,
        ]);

        //WHISKY
        Produit::create([
            'nom' => 'JB 65cl',
            'description' => 'JB Grand format',
            'prix_achat' => 35000,
            'prix_vente' => 60000,
            'categorie_id' => $cat4->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Sir edward 65cl',
            'description' => 'Sir Edward Grand format',
            'prix_achat' => 25000,
            'prix_vente' => 60000,
            'categorie_id' => $cat4->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Red label',
            'description' => 'Red label',
            'prix_achat' => 35000,
            'prix_vente' => 84000,
            'categorie_id' => $cat4->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Henessy',
            'description' => 'Hennessy',
            'prix_achat' => 112000,
            'prix_vente' => 280000,
            'categorie_id' => $cat4->id,
            'point_de_vente_id' => $pdv->id,
        ]);
        Produit::create([
            'nom' => 'Williams',
            'description' => 'williams',
            'prix_achat' => 1200,
            'prix_vente' => 60000,
            'categorie_id' => $cat4->id,
            'point_de_vente_id' => $pdv->id,
        ]);

        // Créer une salle par défaut
        $salle = Salle::create(['nom' => 'Salle principale','entreprise_id' => $entreprise->id]);

        // Lier la salle au point de vente (pivot)
        $pdv->salles()->attach($salle->id);

        // Créer quelques tables dans la salle
        for ($i = 1; $i <= 3; $i++) {
            TableResto::create([
                'numero' =>$i,
                'forme' => 'ronde',
                'position_x' => 100 * $i,
                'position_y' => 50 * $i,
                'salle_id' => $salle->id,
            ]);
        }
        return $pdv;
    }

    /**
     * Initialiser le plan comptable de base pour l'entreprise
     */
    public static function initialiserComptabilite($entreprise)
    {
        // Créer les 7 classes comptables de base si elles n'existent pas
        $classesComptables = [
            [
                'numero' => 1, 
                'nom' => 'Comptes de capitaux', 
                'description' => 'Capital, réserves, résultat',
                'type_document' => 'bilan', 
                'type_nature' => 'passif',
                'est_principale' => true,
                'ordre_affichage' => 1
            ],
            [
                'numero' => 2, 
                'nom' => 'Comptes d\'immobilisations', 
                'description' => 'Immobilisations corporelles et incorporelles',
                'type_document' => 'bilan', 
                'type_nature' => 'actif',
                'est_principale' => true,
                'ordre_affichage' => 2
            ],
            [
                'numero' => 3, 
                'nom' => 'Comptes de stocks et en-cours', 
                'description' => 'Stocks de marchandises, matières premières',
                'type_document' => 'bilan', 
                'type_nature' => 'actif',
                'est_principale' => true,
                'ordre_affichage' => 3
            ],
            [
                'numero' => 4, 
                'nom' => 'Comptes de tiers', 
                'description' => 'Clients, fournisseurs, personnel',
                'type_document' => 'bilan', 
                'type_nature' => 'mixte',
                'est_principale' => true,
                'ordre_affichage' => 4
            ],
            [
                'numero' => 5, 
                'nom' => 'Comptes financiers', 
                'description' => 'Banques, caisses, valeurs mobilières',
                'type_document' => 'bilan', 
                'type_nature' => 'actif',
                'est_principale' => true,
                'ordre_affichage' => 5
            ],
            [
                'numero' => 6, 
                'nom' => 'Comptes de charges', 
                'description' => 'Achats, services extérieurs, personnel',
                'type_document' => 'resultat', 
                'type_nature' => 'charge',
                'est_principale' => true,
                'ordre_affichage' => 6
            ],
            [
                'numero' => 7, 
                'nom' => 'Comptes de produits', 
                'description' => 'Ventes, prestations de services',
                'type_document' => 'resultat', 
                'type_nature' => 'produit',
                'est_principale' => true,
                'ordre_affichage' => 7
            ]
        ];

        foreach ($classesComptables as $classeData) {
            ClasseComptable::firstOrCreate(
                [
                    'numero' => $classeData['numero'],
                    'entreprise_id' => $entreprise->id
                ],
                array_merge($classeData, ['entreprise_id' => $entreprise->id])
            );
        }

        // Créer les comptes de base essentiels
        self::creerComptesDeBBase($entreprise);
    }

    /**
     * Créer les comptes comptables de base pour l'entreprise
     */
    public static function creerComptesDeBBase($entreprise)
    {
        // Récupérer les classes comptables de l'entreprise
        $classe4 = ClasseComptable::where('numero', 4)->where('entreprise_id', $entreprise->id)->first();
        $classe5 = ClasseComptable::where('numero', 5)->where('entreprise_id', $entreprise->id)->first();
        $classe6 = ClasseComptable::where('numero', 6)->where('entreprise_id', $entreprise->id)->first();
        $classe7 = ClasseComptable::where('numero', 7)->where('entreprise_id', $entreprise->id)->first();

        // Vérifier que toutes les classes existent
        if (!$classe4 || !$classe5 || !$classe6 || !$classe7) {
            throw new \Exception('Les classes comptables de base doivent être créées avant les comptes. Classes manquantes.');
        }

        $comptesDeBBase = [
            // COMPTES DE TIERS (Classe 4)
            [
                'numero' => '411000',
                'nom' => 'Clients',
                'description' => 'Créances clients',
                'type' => 'actif',
                'classe_comptable_id' => $classe4->id,
                'entreprise_id' => $entreprise->id,
                'solde_actuel' => 0.00
            ],
            [
                'numero' => '401000',
                'nom' => 'Fournisseurs',
                'description' => 'Dettes fournisseurs',
                'type' => 'passif',
                'classe_comptable_id' => $classe4->id,
                'entreprise_id' => $entreprise->id,
                'solde_actuel' => 0.00
            ],
            
            // COMPTES FINANCIERS (Classe 5)
            [
                'numero' => '512000',
                'nom' => 'Banque',
                'description' => 'Compte bancaire principal',
                'type' => 'actif',
                'classe_comptable_id' => $classe5->id,
                'entreprise_id' => $entreprise->id,
                'solde_actuel' => 0.00
            ],
            [
                'numero' => '530000',
                'nom' => 'Caisse principale',
                'description' => 'Caisse principale de l\'entreprise',
                'type' => 'actif',
                'classe_comptable_id' => $classe5->id,
                'entreprise_id' => $entreprise->id,
                'solde_actuel' => 0.00
            ],
            
            // COMPTES DE CHARGES (Classe 6)
            [
                'numero' => '607000',
                'nom' => 'Achats de marchandises',
                'description' => 'Achats de marchandises destinées à la revente',
                'type' => 'charge',
                'classe_comptable_id' => $classe6->id,
                'entreprise_id' => $entreprise->id,
                'solde_actuel' => 0.00
            ],
            
            // COMPTES DE PRODUITS (Classe 7)
            [
                'numero' => '701000',
                'nom' => 'Ventes de marchandises',
                'description' => 'Chiffre d\'affaires sur ventes de marchandises',
                'type' => 'produit',
                'classe_comptable_id' => $classe7->id,
                'entreprise_id' => $entreprise->id,
                'solde_actuel' => 0.00
            ],
            [
                'numero' => '706000',
                'nom' => 'Prestations de services',
                'description' => 'Chiffre d\'affaires sur prestations de services',
                'type' => 'produit',
                'classe_comptable_id' => $classe7->id,
                'entreprise_id' => $entreprise->id,
                'solde_actuel' => 0.00
            ]
        ];

        foreach ($comptesDeBBase as $compteData) {
            Compte::firstOrCreate(
                [
                    'numero' => $compteData['numero'],
                    'entreprise_id' => $entreprise->id
                ],
                $compteData
            );
        }
    }

    /**
     * Configurer les comptes comptables spécifiques au point de vente
     */
    public static function configurerComptesPointDeVente($pointDeVente, $entreprise)
    {
        // Récupérer les classes comptables
        $classe5 = ClasseComptable::where('numero', 5)->where('entreprise_id', $entreprise->id)->first();
        $classe7 = ClasseComptable::where('numero', 7)->where('entreprise_id', $entreprise->id)->first();

        // Vérifier que les classes existent
        if (!$classe5 || !$classe7) {
            throw new \Exception('Les classes comptables 5 et 7 doivent exister pour configurer les comptes du point de vente.');
        }

        // Créer une caisse spécifique au point de vente
        $compteCaisse = Compte::firstOrCreate(
            [
                'numero' => '530' . str_pad($pointDeVente->id, 3, '0', STR_PAD_LEFT),
                'entreprise_id' => $entreprise->id
            ],
            [
                'nom' => 'Caisse ' . $pointDeVente->nom,
                'description' => 'Caisse du point de vente ' . $pointDeVente->nom,
                'type' => 'actif',
                'classe_comptable_id' => $classe5->id,
                'entreprise_id' => $entreprise->id,
                'solde_actuel' => 0.00
            ]
        );

        // Créer un compte de vente spécifique au point de vente (optionnel)
        $compteVente = Compte::firstOrCreate(
            [
                'numero' => '701' . str_pad($pointDeVente->id, 3, '0', STR_PAD_LEFT),
                'entreprise_id' => $entreprise->id
            ],
            [
                'nom' => 'Ventes ' . $pointDeVente->nom,
                'description' => 'Ventes du point de vente ' . $pointDeVente->nom,
                'type' => 'produit',
                'classe_comptable_id' => $classe7->id,
                'entreprise_id' => $entreprise->id,
                'solde_actuel' => 0.00
            ]
        );

        // Récupérer les comptes existants pour les configurer sur le point de vente
        $compteClients = Compte::where('numero', '411000')->where('entreprise_id', $entreprise->id)->first();

        // Optionnel : Stocker les références des comptes sur le point de vente
        // (vous pouvez ajouter des champs dans la table points_de_vente si nécessaire)
        
        return [
            'caisse' => $compteCaisse,
            'vente' => $compteVente,
            'clients' => $compteClients
        ];
    }
}

// Usage example:
// PosRestaubarDefaults::initialiserPour($module, $entreprise);