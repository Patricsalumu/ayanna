<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Entreprise;
use App\Models\ClasseComptable;

class AssignClassesComptablesToEntreprises extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer toutes les entreprises
        $entreprises = Entreprise::all();
        
        foreach ($entreprises as $entreprise) {
            // Pour chaque entreprise, créer les 7 classes comptables de base
            $classesData = [
                ['numero' => 1, 'nom' => 'Comptes de capitaux', 'type_document' => 'bilan', 'type_nature' => 'passif'],
                ['numero' => 2, 'nom' => 'Comptes d\'immobilisations', 'type_document' => 'bilan', 'type_nature' => 'actif'],
                ['numero' => 3, 'nom' => 'Comptes de stocks et en-cours', 'type_document' => 'bilan', 'type_nature' => 'actif'],
                ['numero' => 4, 'nom' => 'Comptes de tiers', 'type_document' => 'bilan', 'type_nature' => 'mixte'],
                ['numero' => 5, 'nom' => 'Comptes financiers', 'type_document' => 'bilan', 'type_nature' => 'actif'],
                ['numero' => 6, 'nom' => 'Comptes de charges', 'type_document' => 'resultat', 'type_nature' => 'charge'],
                ['numero' => 7, 'nom' => 'Comptes de produits', 'type_document' => 'resultat', 'type_nature' => 'produit'],
            ];

            foreach ($classesData as $index => $classeData) {
                ClasseComptable::updateOrCreate(
                    [
                        'numero' => $classeData['numero'],
                        'entreprise_id' => $entreprise->id
                    ],
                    [
                        'nom' => $classeData['nom'],
                        'description' => 'Classe ' . $classeData['numero'] . ' du plan comptable général',
                        'type_document' => $classeData['type_document'],
                        'type_nature' => $classeData['type_nature'],
                        'est_principale' => true,
                        'classe_parent' => null,
                        'ordre_affichage' => $index + 1,
                        'entreprise_id' => $entreprise->id
                    ]
                );
            }
        }
    }
}
