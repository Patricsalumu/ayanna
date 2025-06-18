<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EntreprisesController;
use App\Http\Controllers\ModulesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\TableRestoController;
use App\Models\Module;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VenteController;


Route::middleware(['auth'])->group(function () 
{
        // Routes clients
        Route::prefix('entreprises/{entreprise}')->group(function () {
        Route::get('clients', [ClientController::class, 'show'])->name('clients.show');
        Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

        // Routes utilisateurs
        Route::get('users', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
        Route::get('users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    });
    
    // Plan de salle en mode vente (aucune modification possible)
    Route::get('/entreprises/{entreprise}/salles/{salle}/plan-vente', [SalleController::class, 'planVente'])->name('salle.plan.vente');
    
    //Routes entreprise
    Route::get('/entreprises/create', [EntreprisesController::class, 'create'])->name('entreprises.create');
    Route::post('/entreprises', [EntreprisesController::class, 'store'])->name('entreprises.store');
    Route::get('/entreprises/edit', [EntreprisesController::class, 'edit'])->name('entreprises.edit');
    Route::post('/entreprises/update', [EntreprisesController::class, 'update'])->name('entreprises.update');
    Route::delete('/entreprises/{entreprise}', [EntreprisesController::class, 'destroy'])->name('entreprises.destroy');
    Route::get('/entreprises/{entreprise}/login', [EntreprisesController::class, 'login'])->name('entreprises.login');
    Route::get('/entreprises/{entreprise}', [EntreprisesController::class, 'show'])->name('entreprises.show');
    
    //Routes modules
    Route::get('/modules/create', [ModulesController::class, 'create'])->name('modules.create');
    Route::post('/modules', [ModulesController::class, 'store'])->name('modules.store');
    Route::get('/modules/{module}/edit', [ModulesController::class, 'edit'])->name('modules.edit');
    Route::put('/modules/{module}', [ModulesController::class, 'update'])->name('modules.update');
    Route::delete('/modules/{module}', [ModulesController::class, 'destroy'])->name('modules.destroy');
    Route::post('/modules/{module}/activate', [ModulesController::class, 'activate'])->name('modules.activate');

    //Routes points de vente
    Route::get('/entreprises/{entreprise}/points-de-vente', [App\Http\Controllers\PointDeVenteController::class, 'show'])
    ->name('pointsDeVente.show');
    Route::get('/entreprises/{entreprise}/points-de-vente/create', [App\Http\Controllers\PointDeVenteController::class, 'create'])->name('pointsDeVente.create');
    Route::post('/entreprises/{entreprise}/points-de-vente', [App\Http\Controllers\PointDeVenteController::class, 'store'])->name('pointsDeVente.store');
    // Duplication d'un point de vente
    Route::post('/entreprises/{entreprise}/points-de-vente/{pointDeVente}/duplicate', [App\Http\Controllers\PointDeVenteController::class, 'duplicate'])->name('pointsDeVente.duplicate');
    //Route pour la modification d'un point de vente
    Route::get('/entreprises/{entreprise}/points-de-vente/{pointDeVente}/edit', [App\Http\Controllers\PointDeVenteController::class, 'edit'])->name('pointsDeVente.edit');
    Route::put('/entreprises/{entreprise}/points-de-vente/{pointDeVente}', [App\Http\Controllers\PointDeVenteController::class, 'update'])->name('pointsDeVente.update');
    Route::delete('/entreprises/{entreprise}/points-de-vente/{pointDeVente}', [App\Http\Controllers\PointDeVenteController::class, 'destroy'])->name('pointsDeVente.destroy');
    
    //Route pour la page de vente
    Route::get('/vente', function () {
        return view('vente.index');
    })->name('vente.index');

    //Route pour les categories
    Route::get('entreprises/{entreprise}/categories', [CategorieController::class, 'show'])->name('categories.show');
    Route::get('entreprises/{entreprise}/categories/create', [CategorieController::class, 'create'])->name('categories.create');
    Route::post('entreprises/{entreprise}/categories', [CategorieController::class, 'store'])->name('categories.store');
    Route::get('entreprises/{entreprise}/categories/{categorie}/edit', [CategorieController::class, 'edit'])->name('categories.edit');
    Route::put('entreprises/{entreprise}/categories/{categorie}', [CategorieController::class, 'update'])->name('categories.update');
    Route::delete('entreprises/{entreprise}/categories/{categorie}', [CategorieController::class, 'destroy'])->name('categories.destroy');

    //Route pour les salles
    Route::get('entreprises/{entreprise}/salles', [SalleController::class, 'show'])->name('salles.show');
    Route::get('entreprises/{entreprise}/salles/create', [SalleController::class, 'create'])->name('salles.create');
    Route::get('/entreprises/{entreprise}/salles/{salle}/plan', [SalleController::class, 'plan'])->name('salle.plan');
    Route::post('entreprises/{entreprise}/salles', [SalleController::class, 'store'])->name('salles.store');
    Route::get('entreprises/{entreprise}/salles/{salle}/edit', [SalleController::class, 'edit'])->name('salles.edit');
    Route::put('entreprises/{entreprise}/salles/{salle}', [SalleController::class, 'update'])->name('salles.update');
    Route::delete('entreprises/{entreprise}/salles/{salle}', [SalleController::class, 'destroy'])->name('salles.destroy');
    Route::resource('tables', TableRestoController::class);

    //Routes pour les tables
    Route::get('/salles/{salle}/tables', [TableRestoController::class, 'getTablesBySalle'])->name('tables.getBySalle');
    Route::get('/salles/{salle}/tables/create', [TableRestoController::class, 'create'])->name('tables.create');
    Route::put('/tables/{table}', [TableRestoController::class, 'update'])->name('tables.update');
    Route::delete('/tables/{table}', [TableRestoController::class, 'destroy'])->name('tables.destroy');
    Route::get('/salles/{salle}/tables/{table}/edit', [TableRestoController::class, 'edit'])->name('tables.edit');
    Route::get('/salles/{salle}/tables/create', [TableRestoController::class, 'create'])->name('tables.create');
    Route::post('/tables', [TableRestoController::class, 'store'])->name('tables.store');

    // Catalogue produits pour la vente (PDV)
    Route::get('/vente/catalogue/{pointDeVente}', [VenteController::class, 'catalogue'])->name('vente.catalogue');
    // Route AJAX pour ajouter un produit au panier (table pivot)
    Route::post('/vente/panier/ajouter/{produitId}', [\App\Http\Controllers\VenteController::class, 'ajouterAuPanier'])->name('vente.panier.ajouter');
    Route::get('/vente/panier/{pointDeVente}', [VenteController::class, 'afficherPanier'])->name('vente.panier');
    
    
    //Route Ajax
    Route::get('/entreprises/{entreprise}/produits/search', [ProduitController::class, 'searchAjax'])->name('produits.searchAjax');

    //Routes produits
    Route::get('/entreprises/{entreprise}/produits/create', [ProduitController::class, 'create'])->name('produits.create');
    Route::post('/entreprises/{entreprise}/produits', [ProduitController::class, 'store'])->name('produits.store');
    Route::get('/entreprises/{entreprise}/produits', [ProduitController::class, 'all'])->name('produits.entreprise');
    Route::get('/entreprises/{entreprise}/produits/{produit}/edit', [ProduitController::class, 'edit'])->name('produits.edit');
    Route::put('/entreprises/{entreprise}/produits/{produit}', [ProduitController::class, 'update'])->name('produits.update');
    Route::post('/entreprises/{entreprise}/produits/{produit}/duplicate', [ProduitController::class, 'duplicate'])->name('produits.duplicate');
    Route::delete('/entreprises/{entreprise}/produits/{produit}', [ProduitController::class, 'destroy'])->name('produits.destroy');

});

// Route pour continuer la vente (accessible uniquement si authentifié)
Route::middleware(['auth'])->group(function () {
    Route::get('/vente/continuer/{id}', [VenteController::class, 'continuer'])->name('vente.continuer');
    Route::get('/vente/ouvrir/{id}', [VenteController::class, 'ouvrir'])->name('vente.ouvrir');
    Route::post('/vente/fermer/{id}', [VenteController::class, 'fermer'])->name('vente.fermer');
    Route::post('/vente/valider', [VenteController::class, 'valider'])->name('vente.valider');
});


Route::get('/', function () {
    return view('welcome');
});

//Route du dashboard

Route::get('/dashboard', function () {
    $modules = Module::all();
    return view('dashboard', compact('modules'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Routes AJAX panier (hors préfixe entreprise)
Route::middleware(['auth'])->group(function () {
    Route::post('/panier/set-client', [\App\Http\Controllers\PanierController::class, 'setClient'])->name('panier.setClient');
    Route::post('/panier/set-serveuse', [\App\Http\Controllers\PanierController::class, 'setServeuse'])->name('panier.setServeuse');
    Route::post('/panier/liberer', [\App\Http\Controllers\PanierController::class, 'libererTable'])->name('panier.libererTable');
});

// Panier (AJAX)
Route::post('/panier/modifier-produit/{produit_id}', [\App\Http\Controllers\PanierController::class, 'modifierProduit'])->name('panier.modifierProduit');
Route::post('/panier/supprimer-produit/{produit_id}', [\App\Http\Controllers\PanierController::class, 'supprimerProduit'])->name('panier.supprimerProduit');

// Liste des paniers du jour (comptoir)
Route::get('/paniers/jour', [\App\Http\Controllers\PanierController::class, 'paniersDuJour'])->name('paniers.jour');

// Annuler un panier
Route::patch('/paniers/{panier}/annuler', [\App\Http\Controllers\PanierController::class, 'annuler'])->name('paniers.annuler');

// Enregistrer un snapshot d'impression de panier
Route::post('/panier/impression/{panier}', [\App\Http\Controllers\PanierController::class, 'enregistrerImpression'])->name('panier.impression');
