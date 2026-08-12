<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class VenteCatalogueFilterTest extends TestCase
{
    public function test_catalogue_view_renders_server_filters_for_categories_and_search(): void
    {
        $user = new class {
            public $id = 1;
            public $role = 'admin';
            public $entreprise_id = 99;
            public $name = 'Test user';
        };

        Auth::shouldReceive('user')->andReturn($user);

        $view = view('vente.catalogue', [
            'pointDeVente' => (object) [
                'id' => 10,
                'nom' => 'Point de vente test',
                'entreprise' => (object) [
                    'id' => 99,
                    'clients' => collect(),
                    'users' => collect(),
                ],
            ],
            'categories' => collect([
                (object) ['id' => 1, 'nom' => 'Boissons'],
            ]),
            'categorieActive' => null,
            'search' => '',
            'produits' => collect(),
            'produitsArray' => [],
            'produitsPanier' => [],
            'clients' => collect(),
            'serveuses' => collect(),
            'tables' => collect(),
            'tableCourante' => null,
            'client_id' => '',
            'serveuse_id' => '',
            'panier' => null,
            'modesPaiement' => collect(),
            'clientsArray' => [],
            'serveusesArray' => [],
            'modesPaiementArray' => [],
        ]);

        $html = $view->render();

        $this->assertStringContainsString('name="categorie"', $html);
        $this->assertStringContainsString('name="search"', $html);
        $this->assertStringContainsString('type="submit"', $html);
    }
}
