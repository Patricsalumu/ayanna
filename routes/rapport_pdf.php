<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RapportController;

// Rapport du jour (point de vente)
Route::get('rapport/jour/{pointDeVenteId}/export-pdf', [RapportController::class, 'exportPdf'])
    ->name('rapport.export_pdf');
