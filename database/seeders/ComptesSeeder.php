<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compte;
use App\Models\Entreprise;

class ComptesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan comptable de base pour chaque entreprise
        $entreprises = Entreprise::all();
        
        foreach ($entreprises as $entreprise) {
            $comptes = [
                // CLASSE 1 - COMPTES DE CAPITAUX
                ['numero' => '101', 'nom' => 'Capital', 'type' => 'passif', 'classe_comptable' => '1', 'solde_initial' => 0],
                ['numero' => '106', 'nom' => 'Réserves', 'type' => 'passif', 'classe_comptable' => '1', 'solde_initial' => 0],
                ['numero' => '110', 'nom' => 'Report à nouveau (solde créditeur)', 'type' => 'passif', 'classe_comptable' => '1', 'solde_initial' => 0],
                ['numero' => '120', 'nom' => 'Résultat de l\'exercice (bénéfice)', 'type' => 'passif', 'classe_comptable' => '1', 'solde_initial' => 0],
                ['numero' => '129', 'nom' => 'Résultat de l\'exercice (perte)', 'type' => 'actif', 'classe_comptable' => '1', 'solde_initial' => 0],
                
                // CLASSE 2 - COMPTES D'IMMOBILISATIONS
                ['numero' => '211', 'nom' => 'Terrains', 'type' => 'actif', 'classe_comptable' => '2', 'solde_initial' => 0],
                ['numero' => '213', 'nom' => 'Constructions', 'type' => 'actif', 'classe_comptable' => '2', 'solde_initial' => 0],
                ['numero' => '218', 'nom' => 'Autres constructions', 'type' => 'actif', 'classe_comptable' => '2', 'solde_initial' => 0],
                ['numero' => '241', 'nom' => 'Matériel et outillage', 'type' => 'actif', 'classe_comptable' => '2', 'solde_initial' => 0],
                ['numero' => '244', 'nom' => 'Matériel de transport', 'type' => 'actif', 'classe_comptable' => '2', 'solde_initial' => 0],
                ['numero' => '245', 'nom' => 'Matériel de bureau et informatique', 'type' => 'actif', 'classe_comptable' => '2', 'solde_initial' => 0],
                
                // CLASSE 3 - COMPTES DE STOCKS
                ['numero' => '311', 'nom' => 'Marchandises', 'type' => 'actif', 'classe_comptable' => '3', 'solde_initial' => 0],
                ['numero' => '321', 'nom' => 'Matières premières', 'type' => 'actif', 'classe_comptable' => '3', 'solde_initial' => 0],
                ['numero' => '322', 'nom' => 'Fournitures', 'type' => 'actif', 'classe_comptable' => '3', 'solde_initial' => 0],
                
                // CLASSE 4 - COMPTES DE TIERS
                ['numero' => '401', 'nom' => 'Fournisseurs', 'type' => 'passif', 'classe_comptable' => '4', 'solde_initial' => 0],
                ['numero' => '411', 'nom' => 'Clients', 'type' => 'actif', 'classe_comptable' => '4', 'solde_initial' => 0],
                ['numero' => '421', 'nom' => 'Personnel - Rémunérations dues', 'type' => 'passif', 'classe_comptable' => '4', 'solde_initial' => 0],
                ['numero' => '431', 'nom' => 'Sécurité sociale', 'type' => 'passif', 'classe_comptable' => '4', 'solde_initial' => 0],
                ['numero' => '441', 'nom' => 'État - Subventions à recevoir', 'type' => 'actif', 'classe_comptable' => '4', 'solde_initial' => 0],
                ['numero' => '445', 'nom' => 'État - Taxes sur le chiffre d\'affaires', 'type' => 'passif', 'classe_comptable' => '4', 'solde_initial' => 0],
                ['numero' => '446', 'nom' => 'État - Autres impôts et taxes', 'type' => 'passif', 'classe_comptable' => '4', 'solde_initial' => 0],
                
                // CLASSE 5 - COMPTES FINANCIERS
                ['numero' => '512', 'nom' => 'Banque', 'type' => 'actif', 'classe_comptable' => '5', 'solde_initial' => 0],
                ['numero' => '530', 'nom' => 'Caisse', 'type' => 'actif', 'classe_comptable' => '5', 'solde_initial' => 0],
                ['numero' => '531', 'nom' => 'Caisse PDV 1', 'type' => 'actif', 'classe_comptable' => '5', 'solde_initial' => 0],
                ['numero' => '532', 'nom' => 'Caisse PDV 2', 'type' => 'actif', 'classe_comptable' => '5', 'solde_initial' => 0],
                ['numero' => '533', 'nom' => 'Comptoir', 'type' => 'actif', 'classe_comptable' => '5', 'solde_initial' => 0],
                
                // CLASSE 6 - COMPTES DE CHARGES
                ['numero' => '601', 'nom' => 'Achats de marchandises', 'type' => 'charge', 'classe_comptable' => '6', 'solde_initial' => 0],
                ['numero' => '621', 'nom' => 'Personnel extérieur', 'type' => 'charge', 'classe_comptable' => '6', 'solde_initial' => 0],
                ['numero' => '622', 'nom' => 'Rémunérations d\'intermédiaires et honoraires', 'type' => 'charge', 'classe_comptable' => '6', 'solde_initial' => 0],
                ['numero' => '623', 'nom' => 'Publicité, publications, relations publiques', 'type' => 'charge', 'classe_comptable' => '6', 'solde_initial' => 0],
                ['numero' => '624', 'nom' => 'Transports de biens et transport collectif du personnel', 'type' => 'charge', 'classe_comptable' => '6', 'solde_initial' => 0],
                ['numero' => '625', 'nom' => 'Déplacements, missions et réceptions', 'type' => 'charge', 'classe_comptable' => '6', 'solde_initial' => 0],
                ['numero' => '641', 'nom' => 'Salaires et appointements', 'type' => 'charge', 'classe_comptable' => '6', 'solde_initial' => 0],
                ['numero' => '645', 'nom' => 'Charges de sécurité sociale et de prévoyance', 'type' => 'charge', 'classe_comptable' => '6', 'solde_initial' => 0],
                ['numero' => '661', 'nom' => 'Charges d\'intérêts', 'type' => 'charge', 'classe_comptable' => '6', 'solde_initial' => 0],
                
                // CLASSE 7 - COMPTES DE PRODUITS
                ['numero' => '701', 'nom' => 'Ventes de marchandises', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '702', 'nom' => 'Ventes de produits finis', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '703', 'nom' => 'Ventes de produits intermédiaires', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '704', 'nom' => 'Travaux', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '705', 'nom' => 'Études', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '706', 'nom' => 'Autres prestations de services', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '708', 'nom' => 'Produits des activités annexes', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '761', 'nom' => 'Produits financiers de participations', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '762', 'nom' => 'Produits des autres immobilisations financières', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '764', 'nom' => 'Revenus des valeurs mobilières de placement', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '765', 'nom' => 'Escomptes obtenus', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
                ['numero' => '768', 'nom' => 'Autres produits financiers', 'type' => 'produit', 'classe_comptable' => '7', 'solde_initial' => 0],
            ];
            
            foreach ($comptes as $compteData) {
                Compte::firstOrCreate(
                    [
                        'numero' => $compteData['numero'],
                        'entreprise_id' => $entreprise->id
                    ],
                    [
                        'nom' => $compteData['nom'],
                        'type' => $compteData['type'],
                        'classe_comptable' => $compteData['classe_comptable'],
                        'solde_initial' => $compteData['solde_initial']
                    ]
                );
            }
        }
    }
}
