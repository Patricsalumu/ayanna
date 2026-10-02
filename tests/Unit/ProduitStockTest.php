<?php

namespace Tests\Unit;

use App\Models\Categorie;
use App\Models\Entreprise;
use App\Models\PointDeVente;
use App\Models\Panier;
use App\Models\Produit;
use App\Models\StockJournalier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProduitStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_for_point_de_vente_uses_latest_session_formula(): void
    {
        $entreprise = Entreprise::create([
            'nom' => 'Ayanna',
            'email' => 'contact@ayanna.test',
            'telephone' => '0000000000',
            'adresse' => 'Adresse test',
            'devise' => 'XAF',
        ]);

        $categorie = Categorie::create([
            'nom' => 'Boissons',
            'entreprise_id' => $entreprise->id,
        ]);

        $pointDeVente = PointDeVente::create([
            'nom' => 'PDV 1',
            'etat' => 'ouvert',
        ]);

        $salleId = DB::table('salles')->insertGetId([
            'nom' => 'Salle test',
            'entreprise_id' => $entreprise->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tableIds = [
            DB::table('table_restos')->insertGetId([
                'salle_id' => $salleId,
                'numero' => 1,
                'forme' => 'carre',
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            DB::table('table_restos')->insertGetId([
                'salle_id' => $salleId,
                'numero' => 2,
                'forme' => 'carre',
                'created_at' => now(),
                'updated_at' => now(),
            ]),
        ];

        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Coca Cola',
            'description' => 'Boisson gazeuse',
            'prix_achat' => 120,
            'prix_vente' => 250,
        ]);

        StockJournalier::create([
            'produit_id' => $produit->id,
            'point_de_vente_id' => $pointDeVente->id,
            'date' => '2026-10-02',
            'session' => '20261002090000',
            'quantite_initiale' => 10,
            'quantite_ajoutee' => 5,
            'quantite_vendue' => 3,
            'quantite_reste' => 12,
        ]);

        StockJournalier::create([
            'produit_id' => $produit->id,
            'point_de_vente_id' => $pointDeVente->id,
            'date' => '2026-10-02',
            'session' => '20261002103000',
            'quantite_initiale' => 12,
            'quantite_ajoutee' => 2,
            'quantite_vendue' => 4,
            'quantite_reste' => 10,
        ]);

        $panierCourant = Panier::create([
            'table_id' => $tableIds[0],
            'point_de_vente_id' => $pointDeVente->id,
            'produits_json' => [],
            'status' => 'en_cours',
        ]);
        $panierCourant->produits()->attach($produit->id, ['quantite' => 2, 'prix' => 250]);

        $autrePanier = Panier::create([
            'table_id' => $tableIds[1],
            'point_de_vente_id' => $pointDeVente->id,
            'produits_json' => [],
            'status' => 'en_cours',
        ]);
        $autrePanier->produits()->attach($produit->id, ['quantite' => 1, 'prix' => 250]);

        $this->assertSame(10, $produit->stockPourPointDeVente($pointDeVente->id));
        $this->assertSame(7, $produit->stockDisponiblePourPointDeVente($pointDeVente->id));
        $this->assertSame(9, $produit->stockDisponiblePourPointDeVente($pointDeVente->id, $panierCourant->id));
    }
}
