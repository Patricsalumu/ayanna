<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-validation-paiement', function () {
    return view('test.validation-paiement');
});

Route::post('/test-validation', function (Illuminate\Http\Request $request) {
    try {
        // Simuler la logique de validation de la méthode valider
        $data = $request->all();
        
        // Validation des données requises de base
        $requiredFields = ['point_de_vente_id', 'table_id', 'mode_paiement'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return response()->json(['error' => "Champ manquant: $field"], 400);
            }
        }

        // Validation conditionnelle pour le paiement par compte client
        if ($data['mode_paiement'] === 'compte_client') {
            $requiredForCompteClient = ['client_id', 'serveuse_id'];
            foreach ($requiredForCompteClient as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return response()->json(['error' => "Pour un paiement par compte client, le champ '$field' est obligatoire"], 400);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Validation réussie',
            'mode_paiement' => $data['mode_paiement'],
            'validation_type' => $data['mode_paiement'] === 'compte_client' ? 'Avec client et serveuse obligatoires' : 'Client et serveuse optionnels'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});
