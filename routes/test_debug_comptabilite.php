<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VenteController;

// Route temporaire pour tester la validation des paiements
Route::post('/debug-validation', function(\Illuminate\Http\Request $request) {
    try {
        $venteController = new VenteController();
        
        // Simuler une requête de validation avec des données minimales
        $request->merge([
            'point_de_vente_id' => 1,
            'table_id' => 1,
            'mode_paiement' => 'especes'
        ]);
        
        return $venteController->valider($request);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
