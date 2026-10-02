<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Entreprise;
use App\Models\PointDeVente;
use App\Models\Produit;
use App\Models\StockJournalier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockJournalierProduitsAbimesTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_session_can_record_damaged_products_and_report_the_quantity(): void
    {
        $this->travelTo(Carbon::parse('2026-10-02 16:00:00'));

        $entreprise = Entreprise::create([
            'nom' => 'Ayanna',
            'email' => 'stock-abime@ayanna.test',
            'telephone' => '0000000000',
            'adresse' => 'Adresse test',
            'devise' => 'XAF',
        ]);
        $user = User::factory()->create([
            'entreprise_id' => $entreprise->id,
            'role' => 'Administrateur',
        ]);
        $pointDeVente = PointDeVente::create([
            'nom' => 'PDV test',
            'etat' => 'ouvert',
            'entreprise_id' => $entreprise->id,
        ]);
        $categorie = Categorie::create([
            'nom' => 'Boissons',
            'entreprise_id' => $entreprise->id,
        ]);
        $pointDeVente->categories()->attach($categorie->id);
        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Jus test',
            'prix_achat' => 50,
            'prix_vente' => 100,
        ]);
        $stock = StockJournalier::create([
            'produit_id' => $produit->id,
            'point_de_vente_id' => $pointDeVente->id,
            'date' => '2026-10-02',
            'session' => '20261002140000',
            'quantite_initiale' => 12,
            'quantite_ajoutee' => 0,
            'quantite_vendue' => 2,
            'quantite_abimee' => 0,
            'quantite_reste' => 10,
        ]);
        $stock->forceFill(['validated_at' => '2026-10-02 14:00:00'])->save();

        $this->actingAs($user)
            ->get(route('stock_journalier.index', $pointDeVente->id))
            ->assertOk()
            ->assertSee('Déclarer un produit abîmé')
            ->assertSee('Q. Abîmée')
            ->assertViewHas('produitsByCategory', function ($categories) {
                return $categories->flatten(1)->first()['q_abimee'] === 0;
            });

        $this->post(route('stock_journalier.store_abime', $pointDeVente->id), [
            'produit_id' => $produit->id,
            'session' => '20261002140000',
            'quantite_abimee' => 3,
        ])->assertRedirect();

        $this->assertSame(3, (int) $stock->fresh()->quantite_abimee);
        $this->assertSame(7, (int) $stock->fresh()->quantite_reste);
        $this->assertSame(2, (int) $stock->fresh()->quantite_vendue);

        $this->actingAs($user)
            ->get(route('stock_journalier.index', $pointDeVente->id))
            ->assertOk()
            ->assertViewHas('produitsByCategory', function ($categories) {
                $product = $categories->flatten(1)->first();
                return $product['q_abimee'] === 3 && $product['q_reste'] === 7;
            });

        $this->get(route('stock_journalier.export_pdf', [
            'pointDeVente' => $pointDeVente->id,
            'session' => '20261002140000',
        ]))->assertDownload();

        $this->get(route('stock_journalier.export_80mm', [
            'pointDeVente' => $pointDeVente->id,
            'session' => '20261002140000',
        ]))->assertDownload();

        $this->from(route('stock_journalier.index', $pointDeVente->id))
            ->post(route('stock_journalier.store_abime', $pointDeVente->id), [
                'produit_id' => $produit->id,
                'session' => '20261002140000',
                'quantite_abimee' => 8,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(3, (int) $stock->fresh()->quantite_abimee);
        $this->assertSame(7, (int) $stock->fresh()->quantite_reste);
    }
}
