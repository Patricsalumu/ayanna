<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Entreprise;
use App\Models\Panier;
use App\Models\PointDeVente;
use App\Models\Produit;
use App\Models\StockJournalier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RapportSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_defaults_to_the_open_session_instead_of_the_whole_day(): void
    {
        $this->travelTo(Carbon::parse('2026-10-02 16:00:00'));

        $entreprise = Entreprise::create([
            'nom' => 'Ayanna',
            'email' => 'rapport-session@ayanna.test',
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
        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Jus test',
            'prix_achat' => 5,
            'prix_vente' => 10,
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

        foreach ([
            ['session' => '20261002090000', 'opened_at' => '2026-10-02 09:00:00'],
            ['session' => '20261002140000', 'opened_at' => '2026-10-02 14:00:00'],
        ] as $sessionData) {
            $stock = StockJournalier::create([
                'produit_id' => $produit->id,
                'point_de_vente_id' => $pointDeVente->id,
                'date' => '2026-10-02',
                'session' => $sessionData['session'],
                'quantite_initiale' => 10,
                'quantite_reste' => 10,
            ]);
            $stock->forceFill(['validated_at' => $sessionData['opened_at']])->save();
        }

        foreach ([
            ['mode' => 'especes', 'created_at' => '2026-10-02 10:00:00', 'amount' => 20],
            ['mode' => 'especes', 'created_at' => '2026-10-02 15:00:00', 'amount' => 30],
        ] as $saleData) {
            $panier = Panier::create([
                'table_id' => $tableId,
                'point_de_vente_id' => $pointDeVente->id,
                'produits_json' => [],
                'status' => 'validé',
            ]);
            $panier->produits()->attach($produit->id, [
                'quantite' => $saleData['amount'] / 10,
                'prix' => 10,
            ]);
            Commande::create([
                'panier_id' => $panier->id,
                'point_de_vente_id' => $pointDeVente->id,
                'date_commande' => '2026-10-02',
                'mode_paiement' => $saleData['mode'],
                'statut' => 'validé',
                'created_at' => $saleData['created_at'],
            ]);
        }

        $this->assertSame(1, Commande::whereBetween('created_at', [
            '2026-10-02 14:00:00',
            '2026-10-02 16:00:00',
        ])->whereHas('panier', fn ($query) => $query->where('point_de_vente_id', $pointDeVente->id))->count());
        $commandeSession = Commande::where('created_at', '2026-10-02 15:00:00')->first();
        $this->assertNotNull($commandeSession?->panier);
        $this->assertSame(1, $commandeSession->panier->produits->count());
        $this->assertEquals(30, $commandeSession->panier->produits->sum(fn ($item) => $item->pivot->quantite * $item->pivot->prix));

        $response = $this->actingAs($user)->get(route('rapport.jour', $pointDeVente->id));

        $response->assertOk()
            ->assertViewHas('selectedSessionFrom', '20261002140000')
            ->assertViewHas('selectedSessionTo', '20261002140000')
            ->assertViewHas('recettesVentes', 30.0);
    }
}
