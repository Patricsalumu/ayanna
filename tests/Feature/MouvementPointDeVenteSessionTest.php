<?php

namespace Tests\Feature;

use App\Models\Compte;
use App\Models\Categorie;
use App\Models\Entreprise;
use App\Models\EntreeSortie;
use App\Models\Historiquepdv;
use App\Models\PointDeVente;
use App\Models\Produit;
use App\Models\StockJournalier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MouvementPointDeVenteSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_view_movements_and_access_the_create_endpoint(): void
    {
        $entreprise = Entreprise::create([
            'nom' => 'Ayanna',
            'email' => 'mouvements-caissier@ayanna.test',
            'telephone' => '0000000000',
            'adresse' => 'Adresse test',
            'devise' => 'XAF',
        ]);
        $user = User::factory()->create([
            'entreprise_id' => $entreprise->id,
            'role' => 'Caissier',
        ]);
        $pointDeVente = PointDeVente::create([
            'nom' => 'PDV caissier',
            'etat' => 'ouvert',
            'entreprise_id' => $entreprise->id,
        ]);
        $compte = Compte::create([
            'numero' => '7580',
            'nom' => 'Autres produits',
            'type' => 'passif',
            'entreprise_id' => $entreprise->id,
        ]);
        $compteCaisse = Compte::create([
            'numero' => '5701',
            'nom' => 'Caisse test',
            'type' => 'actif',
            'entreprise_id' => $entreprise->id,
        ]);
        $pointDeVente->update(['compte_caisse_id' => $compteCaisse->id]);
        $categorie = Categorie::create([
            'nom' => 'Test',
            'entreprise_id' => $entreprise->id,
        ]);
        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Produit test',
            'prix_achat' => 0,
            'prix_vente' => 0,
        ]);
        $sessionStart = now();
        $stock = StockJournalier::create([
            'produit_id' => $produit->id,
            'point_de_vente_id' => $pointDeVente->id,
            'date' => $sessionStart->toDateString(),
            'session' => $sessionStart->format('YmdHis'),
            'quantite_initiale' => 0,
        ]);
        $stock->forceFill(['validated_at' => $sessionStart])->save();

        $this->actingAs($user)
            ->get(route('mouvements.pdv', $pointDeVente->id))
            ->assertOk()
            ->assertSee('Nouveau mouvement');

        foreach (['entree', 'sortie'] as $type) {
            $this->actingAs($user)
                ->post(route('mouvements.pdv.store', $pointDeVente->id), [
                'compte_id' => $compte->id,
                'type_mouvement' => $type,
                'montant' => 100,
                'libele' => 'Mouvement caissier ' . $type,
            ])
                ->assertRedirect(route('mouvements.pdv', $pointDeVente->id))
                ->assertSessionHas('success');
        }

        $this->assertEqualsCanonicalizing(
            ['entree', 'sortie'],
            EntreeSortie::where('point_de_vente_id', $pointDeVente->id)->pluck('type')->all()
        );
    }

    public function test_movement_list_only_shows_current_session_for_requested_pdv(): void
    {
        $this->travelTo(Carbon::parse('2026-10-02 16:00:00'));

        $entreprise = Entreprise::create([
            'nom' => 'Ayanna',
            'email' => 'mouvements-session@ayanna.test',
            'telephone' => '0000000000',
            'adresse' => 'Adresse test',
            'devise' => 'XAF',
        ]);
        $user = User::factory()->create([
            'entreprise_id' => $entreprise->id,
            'role' => 'Administrateur',
        ]);
        $pointDeVenteUn = PointDeVente::create([
            'nom' => 'PDV un',
            'etat' => 'ouvert',
            'entreprise_id' => $entreprise->id,
        ]);
        $pointDeVenteDeux = PointDeVente::create([
            'nom' => 'PDV deux',
            'etat' => 'ouvert',
            'entreprise_id' => $entreprise->id,
        ]);
        $compte = Compte::create([
            'numero' => '5701',
            'nom' => 'Caisse test',
            'type' => 'actif',
            'entreprise_id' => $entreprise->id,
        ]);
        $categorie = Categorie::create([
            'nom' => 'Test',
            'entreprise_id' => $entreprise->id,
        ]);
        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Produit test',
            'prix_achat' => 0,
            'prix_vente' => 0,
        ]);

        foreach ([
            [$pointDeVenteUn, '20261002090000', '2026-10-02 09:00:00'],
            [$pointDeVenteUn, '20261002140000', '2026-10-02 14:00:00'],
            [$pointDeVenteDeux, '20261002130000', '2026-10-02 13:00:00'],
        ] as [$pointDeVente, $session, $openedAt]) {
            $stock = StockJournalier::create([
                'produit_id' => $produit->id,
                'point_de_vente_id' => $pointDeVente->id,
                'date' => substr($openedAt, 0, 10),
                'session' => $session,
                'quantite_initiale' => 0,
            ]);
            $stock->forceFill(['validated_at' => $openedAt])->save();
        }
        Historiquepdv::create([
            'point_de_vente_id' => $pointDeVenteUn->id,
            'user_id' => $user->id,
            'etat' => 'ferme',
            'opened_at' => '2026-10-02 09:00:00',
            'closed_at' => '2026-10-02 13:30:00',
            'closed_by' => $user->id,
        ]);

        $mouvements = [];
        foreach ([
            [$pointDeVenteUn, '2026-10-02 10:00:00', 10],
            [$pointDeVenteUn, '2026-10-02 15:00:00', 30],
            [$pointDeVenteDeux, '2026-10-02 15:00:00', 50],
        ] as [$pointDeVente, $createdAt, $montant]) {
            $mouvement = EntreeSortie::create([
                'compte_id' => $compte->id,
                'montant' => $montant,
                'libele' => 'Mouvement test ' . $montant,
                'type' => 'entree',
                'user_id' => $user->id,
                'point_de_vente_id' => $pointDeVente->id,
            ]);
            $mouvement->forceFill(['created_at' => $createdAt])->save();
            $mouvements[] = $mouvement;
        }

        $response = $this->actingAs($user)->get(route('mouvements.pdv', $pointDeVenteUn->id));

        $response->assertOk()
            ->assertViewHas('mouvements', function ($rows) use ($mouvements) {
                return $rows->count() === 1 && $rows->first()->id === $mouvements[1]->id;
            })
            ->assertViewHas('totalEntree', 30.0)
            ->assertSee('Session du 02/10/2026 14:00 au 02/10/2026 16:00')
            ->assertDontSee('Mouvement test 10')
            ->assertDontSee('Mouvement test 50');

        $previousSessionResponse = $this->actingAs($user)->get(route('mouvements.pdv', [
            'pointDeVente' => $pointDeVenteUn->id,
            'session_from' => '20261002090000',
            'session_to' => '20261002090000',
        ]));

        $previousSessionResponse->assertOk()
            ->assertViewHas('selectedSessionFrom', '20261002090000')
            ->assertViewHas('selectedSessionTo', '20261002090000')
            ->assertViewHas('mouvements', function ($rows) use ($mouvements) {
                return $rows->count() === 1 && $rows->first()->id === $mouvements[0]->id;
            })
            ->assertViewHas('totalEntree', 10.0)
            ->assertSee('Session du 02/10/2026 09:00 au 02/10/2026 13:30');
    }
}
