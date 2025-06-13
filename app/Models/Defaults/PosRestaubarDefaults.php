<?php
namespace App\Models\Defaults;

use App\Models\PointDeVente;
use App\Models\Salle;
use App\Models\TableResto;
use App\Models\Categorie;
use App\Models\Produit;

class PosRestaubarDefaults
{
    public static function initialiserPour($module, $entreprise)
    {
        // Créer un point de vente par défaut
        $pdv = PointDeVente::create([
            'nom' => 'Restaurant',
            'module_id' => $module->id,
            'entreprise_id' => $entreprise->id,
        ]);

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
        ]);
            Produit::create([
            'nom' => 'Castel lite',
            'description' => 'Bière blonde 33cl',
            'prix_achat' => 2000,
            'prix_vente' => 4000,
            'categorie_id' => $cat1->id,
        ]);
            Produit::create([
            'nom' => 'Heineken',
            'description' => 'Bière blonde 33cl',
            'prix_achat' => 2000,
            'prix_vente' => 4000,
            'categorie_id' => $cat1->id,
        ]);
            Produit::create([
            'nom' => 'Simba',
            'description' => 'Bière brasimba 73 cl',
            'prix_achat' =>3500,
            'prix_vente' => 6000,
            'categorie_id' => $cat1->id,
        ]);
            Produit::create([
            'nom' => 'Tembo',
            'description' => 'Bière brasimba 73cl',
            'prix_achat' => 3500,
            'prix_vente' => 6000,
            'categorie_id' => $cat1->id,
        ]);
            Produit::create([
            'nom' => 'Castel',
            'description' => 'Bière brasimba 67cl',
            'prix_achat' => 3500,
            'prix_vente' => 6000,
            'categorie_id' => $cat1->id,
        ]);
    //categorie cuisine
        Produit::create([
            'nom' => 'Tilapia',
            'description' => 'Plat accompagné',
            'prix_achat' => 10000,
            'prix_vente' => 30000,
            'categorie_id' => $cat2->id,
        ]);
            Produit::create([
            'nom' => 'Poulet complet',
            'description' => 'Poulet complet ',
            'prix_achat' => 15000,
            'prix_vente' => 40000,
            'categorie_id' => $cat2->id,
        ]);
            Produit::create([
            'nom' => 'Demi Poulet',
            'description' => 'Plat complet viande grillée',
            'prix_achat' => 8000,
            'prix_vente' => 25000,
            'categorie_id' => $cat2->id,
        ]);
            Produit::create([
            'nom' => 'Ailes de poulet + Frites',
            'description' => 'Plat complet viande grillée',
            'prix_achat' => 12000,
            'prix_vente' => 25000,
            'categorie_id' => $cat2->id,
        ]);
                Produit::create([
            'nom' => 'Boulettes sipmle',
            'description' => 'Boulette simple non accompagné',
            'prix_achat' => 7500,
            'prix_vente' => 15000,
            'categorie_id' => $cat2->id,
        ]);
       //Categorie sucrés
         Produit::create([
            'nom' => 'Coca cola',
            'description' => 'Plat complet viande grillée',
            'prix_achat' => 1200,
            'prix_vente' => 3000,
            'categorie_id' => $cat3->id,
        ]);
            Produit::create([
            'nom' => 'Coca cola Gf',
            'description' => 'bouteille grand format',
            'prix_achat' => 1200,
            'prix_vente' => 5000,
            'categorie_id' => $cat3->id,
        ]);
            Produit::create([
            'nom' => 'Djino',
            'description' => 'bouteille cassable 33cl',
            'prix_achat' => 1200,
            'prix_vente' => 3000,
            'categorie_id' => $cat3->id,
        ]);
            Produit::create([
            'nom' => 'Jus ceres',
            'description' => 'carton',
            'prix_achat' => 3000,
            'prix_vente' => 6000,
            'categorie_id' => $cat3->id,
        ]);
            Produit::create([
            'nom' => 'Pepsi',
            'description' => 'bouteille plastique',
            'prix_achat' => 1200,
            'prix_vente' => 3000,
            'categorie_id' => $cat3->id,
        ]);

        //WHISKY
            Produit::create([
            'nom' => 'JB 65cl',
            'description' => 'JB Grand format',
            'prix_achat' => 35000,
            'prix_vente' => 60000,
            'categorie_id' => $cat4->id,
        ]);
            Produit::create([
            'nom' => 'Sir edward 65cl',
            'description' => 'Sir Edward Grand format',
            'prix_achat' => 25000,
            'prix_vente' => 60000,
            'categorie_id' => $cat4->id,
        ]);
            Produit::create([
            'nom' => 'Red label',
            'description' => 'Red label',
            'prix_achat' => 35000,
            'prix_vente' => 84000,
            'categorie_id' => $cat4->id,
        ]);
            Produit::create([
            'nom' => 'Henessy',
            'description' => 'Hennessy',
            'prix_achat' => 112000,
            'prix_vente' => 280000,
            'categorie_id' => $cat4->id,
        ]);
            Produit::create([
            'nom' => 'Williams',
            'description' => 'williams',
            'prix_achat' => 1200,
            'prix_vente' => 60000,
            'categorie_id' => $cat4->id,
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
    }
}
// Usage example:
// PosRestaubarDefaults::initialiserPour($module, $entreprise);