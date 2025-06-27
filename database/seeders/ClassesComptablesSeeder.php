<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClasseComptable;

class ClassesComptablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            // CLASSES PRINCIPALES DU PLAN COMPTABLE GÉNÉRAL
            [
                'numero' => '1',
                'nom' => 'Comptes de capitaux',
                'description' => 'Capital, réserves, résultat, emprunts et dettes financières',
                'type_document' => 'bilan',
                'type_nature' => 'passif',
                'est_principale' => true,
                'classe_parent' => null,
                'ordre_affichage' => 1
            ],
            [
                'numero' => '2',
                'nom' => 'Comptes d\'immobilisations',
                'description' => 'Immobilisations incorporelles, corporelles et financières',
                'type_document' => 'bilan',
                'type_nature' => 'actif',
                'est_principale' => true,
                'classe_parent' => null,
                'ordre_affichage' => 2
            ],
            [
                'numero' => '3',
                'nom' => 'Comptes de stocks',
                'description' => 'Stocks de marchandises, matières premières, produits finis',
                'type_document' => 'bilan',
                'type_nature' => 'actif',
                'est_principale' => true,
                'classe_parent' => null,
                'ordre_affichage' => 3
            ],
            [
                'numero' => '4',
                'nom' => 'Comptes de tiers',
                'description' => 'Clients, fournisseurs, personnel, organismes sociaux, État',
                'type_document' => 'bilan',
                'type_nature' => 'actif',
                'est_principale' => true,
                'classe_parent' => null,
                'ordre_affichage' => 4
            ],
            [
                'numero' => '5',
                'nom' => 'Comptes financiers',
                'description' => 'Banques, caisses, valeurs mobilières de placement',
                'type_document' => 'bilan',
                'type_nature' => 'actif',
                'est_principale' => true,
                'classe_parent' => null,
                'ordre_affichage' => 5
            ],
            [
                'numero' => '6',
                'nom' => 'Comptes de charges',
                'description' => 'Achats, services extérieurs, charges de personnel, charges financières',
                'type_document' => 'resultat',
                'type_nature' => 'charge',
                'est_principale' => true,
                'classe_parent' => null,
                'ordre_affichage' => 6
            ],
            [
                'numero' => '7',
                'nom' => 'Comptes de produits',
                'description' => 'Ventes, prestations de services, produits financiers',
                'type_document' => 'resultat',
                'type_nature' => 'produit',
                'est_principale' => true,
                'classe_parent' => null,
                'ordre_affichage' => 7
            ],

            // SOUS-CLASSES COURANTES POUR LES CHARGES (Classe 6)
            [
                'numero' => '60',
                'nom' => 'Achats',
                'description' => 'Achats de marchandises, matières premières, fournitures',
                'type_document' => 'resultat',
                'type_nature' => 'charge',
                'est_principale' => false,
                'classe_parent' => '6',
                'ordre_affichage' => 601
            ],
            [
                'numero' => '61',
                'nom' => 'Services extérieurs',
                'description' => 'Sous-traitance, locations, entretien et réparations',
                'type_document' => 'resultat',
                'type_nature' => 'charge',
                'est_principale' => false,
                'classe_parent' => '6',
                'ordre_affichage' => 602
            ],
            [
                'numero' => '62',
                'nom' => 'Autres services extérieurs',
                'description' => 'Rémunérations intermédiaires, publicité, déplacements',
                'type_document' => 'resultat',
                'type_nature' => 'charge',
                'est_principale' => false,
                'classe_parent' => '6',
                'ordre_affichage' => 603
            ],
            [
                'numero' => '63',
                'nom' => 'Impôts et taxes',
                'description' => 'Impôts et taxes sur rémunérations, autres impôts',
                'type_document' => 'resultat',
                'type_nature' => 'charge',
                'est_principale' => false,
                'classe_parent' => '6',
                'ordre_affichage' => 604
            ],
            [
                'numero' => '64',
                'nom' => 'Charges de personnel',
                'description' => 'Rémunérations, charges sociales',
                'type_document' => 'resultat',
                'type_nature' => 'charge',
                'est_principale' => false,
                'classe_parent' => '6',
                'ordre_affichage' => 605
            ],
            [
                'numero' => '65',
                'nom' => 'Autres charges de gestion courante',
                'description' => 'Pertes sur créances, charges diverses de gestion',
                'type_document' => 'resultat',
                'type_nature' => 'charge',
                'est_principale' => false,
                'classe_parent' => '6',
                'ordre_affichage' => 606
            ],
            [
                'numero' => '66',
                'nom' => 'Charges financières',
                'description' => 'Intérêts des emprunts, pertes de change',
                'type_document' => 'resultat',
                'type_nature' => 'charge',
                'est_principale' => false,
                'classe_parent' => '6',
                'ordre_affichage' => 607
            ],

            // SOUS-CLASSES COURANTES POUR LES PRODUITS (Classe 7)
            [
                'numero' => '70',
                'nom' => 'Ventes de produits finis',
                'description' => 'Ventes de produits finis, déchets et rebuts',
                'type_document' => 'resultat',
                'type_nature' => 'produit',
                'est_principale' => false,
                'classe_parent' => '7',
                'ordre_affichage' => 701
            ],
            [
                'numero' => '71',
                'nom' => 'Production stockée',
                'description' => 'Variation des stocks de produits',
                'type_document' => 'resultat',
                'type_nature' => 'produit',
                'est_principale' => false,
                'classe_parent' => '7',
                'ordre_affichage' => 702
            ],
            [
                'numero' => '72',
                'nom' => 'Production immobilisée',
                'description' => 'Production immobilisée incorporelle et corporelle',
                'type_document' => 'resultat',
                'type_nature' => 'produit',
                'est_principale' => false,
                'classe_parent' => '7',
                'ordre_affichage' => 703
            ],
            [
                'numero' => '74',
                'nom' => 'Subventions d\'exploitation',
                'description' => 'Subventions reçues de l\'État, collectivités',
                'type_document' => 'resultat',
                'type_nature' => 'produit',
                'est_principale' => false,
                'classe_parent' => '7',
                'ordre_affichage' => 704
            ],
            [
                'numero' => '75',
                'nom' => 'Autres produits de gestion courante',
                'description' => 'Produits divers de gestion courante',
                'type_document' => 'resultat',
                'type_nature' => 'produit',
                'est_principale' => false,
                'classe_parent' => '7',
                'ordre_affichage' => 705
            ],
            [
                'numero' => '76',
                'nom' => 'Produits financiers',
                'description' => 'Produits de participations, intérêts, gains de change',
                'type_document' => 'resultat',
                'type_nature' => 'produit',
                'est_principale' => false,
                'classe_parent' => '7',
                'ordre_affichage' => 706
            ]
        ];

        foreach ($classes as $classe) {
            ClasseComptable::updateOrCreate(
                ['numero' => $classe['numero']],
                $classe
            );
        }
    }
}
