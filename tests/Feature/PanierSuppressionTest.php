<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Entreprise;
use App\Models\Panier;
use App\Models\PointDeVente;
use App\Models\Produit;
use App\Models\Salle;
use App\Models\TableResto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanierSuppressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_panier_est_sauvegarde_en_base_au_clique_sur_commande_finale(): void
    {
        $entreprise = Entreprise::create([
            'nom' => 'Entreprise test',
            'module' => 'restaurant',
            'devise' => 'F',
            'taux' => 1,
        ]);

        $serveuse = User::create([
            'name' => 'Serveuse',
            'email' => 'serveuse2@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'Serveuse',
            'entreprise_id' => $entreprise->id,
        ]);

        $pointDeVente = PointDeVente::create([
            'nom' => 'PDV 2',
            'entreprise_id' => $entreprise->id,
            'etat' => 'ouvert',
        ]);

        $salle = Salle::create([
            'nom' => 'Salle 2',
            'entreprise_id' => $entreprise->id,
        ]);

        $pointDeVente->salles()->attach($salle->id);

        $table = TableResto::create([
            'numero' => '2',
            'salle_id' => $salle->id,
            'serveuse_id' => $serveuse->id,
        ]);

        $categorie = Categorie::create([
            'nom' => 'Boissons',
            'entreprise_id' => $entreprise->id,
        ]);

        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Coca',
            'prix_achat' => 200,
            'prix_vente' => 500,
        ]);

        $panier = Panier::create([
            'table_id' => $table->id,
            'point_de_vente_id' => $pointDeVente->id,
            'status' => 'en_cours',
            'serveuse_id' => $serveuse->id,
        ]);

        $this->actingAs($serveuse)->post('/vente/panier/sync', [
            'table_id' => $table->id,
            'point_de_vente_id' => $pointDeVente->id,
            'panier' => [[
                'id' => $produit->id,
                'qte' => 2,
                'prix' => 500,
                'nom' => 'Coca',
            ]],
        ])->assertOk()->assertJsonPath('success', true);

        $panier->refresh();
        $this->assertSame(2, $panier->produits()->first()->pivot->quantite);
    }

    public function test_une_serveuse_peut_supprimer_un_produit_avec_le_mot_de_passe_admin(): void
    {
        $entreprise = Entreprise::create([
            'nom' => 'Entreprise test',
            'module' => 'restaurant',
            'devise' => 'F',
            'taux' => 1,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'Administrateur',
            'entreprise_id' => $entreprise->id,
        ]);

        $serveuse = User::create([
            'name' => 'Serveuse',
            'email' => 'serveuse@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'Serveuse',
            'entreprise_id' => $entreprise->id,
        ]);

        $pointDeVente = PointDeVente::create([
            'nom' => 'PDV 1',
            'entreprise_id' => $entreprise->id,
            'etat' => 'ouvert',
        ]);

        $salle = Salle::create([
            'nom' => 'Salle 1',
            'entreprise_id' => $entreprise->id,
        ]);

        $pointDeVente->salles()->attach($salle->id);

        $table = TableResto::create([
            'numero' => '1',
            'salle_id' => $salle->id,
            'serveuse_id' => $serveuse->id,
        ]);

        $categorie = Categorie::create([
            'nom' => 'Boissons',
            'entreprise_id' => $entreprise->id,
        ]);

        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Coca',
            'prix_achat' => 200,
            'prix_vente' => 500,
        ]);

        $panier = Panier::create([
            'table_id' => $table->id,
            'point_de_vente_id' => $pointDeVente->id,
            'status' => 'en_cours',
            'serveuse_id' => $serveuse->id,
        ]);

        $panier->produits()->attach($produit->id, [
            'quantite' => 1,
            'prix' => $produit->prix_vente,
        ]);

        $response = $this->actingAs($serveuse)->post(route('panier.supprimerProduit', $produit->id), [
            'table_id' => $table->id,
            'point_de_vente_id' => $pointDeVente->id,
            'password_admin' => 'secret123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertSame([], $response->json('panier'));

        $panier->refresh();
        $this->assertSame(0, $panier->produits()->first()?->pivot?->quantite ?? 0);
    }
}
