<?php

namespace Tests\Feature;

use App\Models\BonCommande;
use App\Models\Panier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServeuseLogoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_serveuse_logout_keeps_the_authenticated_session_alive(): void
    {
        $user = User::factory()->create([
            'role' => 'Serveuse',
            'email' => 'serveuse@example.com',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('serveuse.logout', ['serveuse_logout' => 1]));

        $response->assertRedirect(route('serveuse.login'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_dernier_bon_returns_products_for_printing(): void
    {
        $user = User::factory()->create([
            'role' => 'Serveuse',
            'email' => 'serveuse2@example.com',
        ]);

        $this->actingAs($user);

        $panier = Panier::create([
            'table_id' => 1,
            'point_de_vente_id' => 1,
            'serveuse_id' => $user->id,
            'opened_by' => $user->id,
            'produits_json' => [],
            'status' => 'en_cours',
        ]);

        BonCommande::create([
            'numero_bon' => 42,
            'panier_id' => $panier->id,
            'serveuse_id' => $user->id,
            'utilisateur_id' => $user->id,
            'produits_json' => [
                ['produit_id' => 10, 'nom' => 'Coca', 'quantite' => 2],
                ['produit_id' => 11, 'nom' => 'Burger', 'quantite' => 1],
            ],
        ]);

        $response = $this->getJson(route('bon-commande.panier.last', ['panierId' => $panier->id]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('numero_bon', 42)
            ->assertJsonCount(2, 'produits');
    }
}
