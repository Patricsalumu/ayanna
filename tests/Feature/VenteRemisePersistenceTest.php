<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Entreprise;
use App\Models\ModePaiement;
use App\Models\Panier;
use App\Models\PointDeVente;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VenteRemisePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_payment_keeps_admin_discount_saved_on_the_pantry(): void
    {
        $entreprise = Entreprise::create([
            'nom' => 'Ayanna',
            'email' => 'remise-paiement@ayanna.test',
            'telephone' => '0000000000',
            'adresse' => 'Adresse test',
            'devise' => 'XAF',
        ]);
        $caissier = User::factory()->create([
            'entreprise_id' => $entreprise->id,
            'role' => 'Caissier',
        ]);
        $pointDeVente = PointDeVente::create([
            'nom' => 'PDV test',
            'etat' => 'ouvert',
            'entreprise_id' => $entreprise->id,
        ]);
        ModePaiement::create([
            'nom' => 'Espèces',
            'code' => 'especes',
            'actif' => true,
            'est_systeme' => true,
            'ordre' => 10,
            'entreprise_id' => $entreprise->id,
        ]);
        $categorie = Categorie::create([
            'nom' => 'Boissons',
            'entreprise_id' => $entreprise->id,
        ]);
        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Jus test',
            'prix_achat' => 50,
            'prix_vente' => 100,
        ]);
        $salleId = DB::table('salles')->insertGetId([
            'nom' => 'Salle test',
            'entreprise_id' => $entreprise->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tableId = DB::table('table_restos')->insertGetId([
            'salle_id' => $salleId,
            'numero' => 1,
            'forme' => 'carre',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $panier = Panier::create([
            'table_id' => $tableId,
            'point_de_vente_id' => $pointDeVente->id,
            'produits_json' => [],
            'status' => 'en_cours',
        ]);
        $panier->produits()->attach($produit->id, ['quantite' => 1, 'prix' => 100]);
        $panier->forceFill([
            'total_ht' => 100,
            'total_remise' => 5,
            'total_ttc' => 95,
        ])->save();

        $response = $this->actingAs($caissier)->postJson(route('vente.valider'), [
            'point_de_vente_id' => $pointDeVente->id,
            'table_id' => $tableId,
            'mode_paiement' => 'especes',
            'montant_recu' => 95,
            'remise' => 0,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame(5.0, (float) $panier->fresh()->total_remise);
        $this->assertSame(95.0, (float) $panier->fresh()->total_ttc);
        $this->assertSame('validé', $panier->fresh()->status);
    }
}
