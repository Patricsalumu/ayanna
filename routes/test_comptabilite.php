<?php

use Illuminate\Support\Facades\Route;
use App\Models\Entreprise;
use App\Models\PointDeVente;
use App\Models\Defaults\PosRestaubarDefaults;

Route::get('/test-comptabilite', function () {
    try {
        // Créer ou récupérer une entreprise de test
        $entreprise = Entreprise::firstOrCreate([
            'nom' => 'Test Entreprise',
            'email' => 'test@example.com'
        ], [
            'adresse' => 'Test Address',
            'telephone' => '123456789',
            'siret' => '12345678901234',
            'code_ape' => '5610A',
            'numero_tva_intracommunautaire' => 'FR12345678901',
        ]);

        // Créer ou récupérer un module de test
        $module = \App\Models\Module::firstOrCreate([
            'nom' => 'Test Module'
        ], [
            'description' => 'Module de test',
        ]);

        // Créer ou récupérer un point de vente de test
        $pointDeVente = PointDeVente::firstOrCreate([
            'nom' => 'Test POS',
            'entreprise_id' => $entreprise->id
        ], [
            'adresse' => 'Test POS Address',
            'telephone' => '987654321',
            'type' => 'restaurant',
            'module_id' => $module->id,
        ]);

        // Initialiser la comptabilité
        $defaults = new PosRestaubarDefaults();
        $result1 = $defaults::initialiserComptabilite($entreprise);
        $result2 = $defaults::configurerComptesPointDeVente($pointDeVente, $entreprise);

        // Récupérer les classes comptables créées
        $classesComptables = \App\Models\ClasseComptable::where('entreprise_id', $entreprise->id)->get();

        // Récupérer les comptes créés
        $comptes = \App\Models\Compte::where('entreprise_id', $entreprise->id)->get();

        return response()->json([
            'success' => true,
            'entreprise' => $entreprise,
            'point_de_vente' => $pointDeVente,
            'initialization_result' => ['comptabilite' => $result1, 'comptes_pdv' => $result2],
            'classes_comptables' => $classesComptables,
            'comptes' => $comptes,
            'stats' => [
                'nombre_classes' => $classesComptables->count(),
                'nombre_comptes' => $comptes->count(),
            ]
        ], 200, [], JSON_PRETTY_PRINT);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500, [], JSON_PRETTY_PRINT);
    }
});
