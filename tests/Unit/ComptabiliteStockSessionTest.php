<?php

namespace Tests\Unit;

use App\Models\Categorie;
use App\Models\ClasseComptable;
use App\Models\Compte;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Entreprise;
use App\Models\JournalComptable;
use App\Models\ModePaiement;
use App\Models\Paiement;
use App\Models\Panier;
use App\Models\PointDeVente;
use App\Models\Produit;
use App\Models\StockJournalier;
use App\Models\User;
use App\Services\ComptabiliteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComptabiliteStockSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_product_does_not_block_stock_journal_for_other_sold_products(): void
    {
        $entreprise = Entreprise::create([
            'nom' => 'Ayanna',
            'email' => 'contact@ayanna.test',
            'telephone' => '0000000000',
            'adresse' => 'Adresse test',
            'devise' => 'XAF',
        ]);
        $user = User::factory()->create(['entreprise_id' => $entreprise->id]);
        $classeStock = ClasseComptable::create([
            'numero' => '3',
            'nom' => 'Stocks',
            'type_document' => 'bilan',
            'type_nature' => 'actif',
            'entreprise_id' => $entreprise->id,
        ]);
        $classeClient = ClasseComptable::create([
            'numero' => '4',
            'nom' => 'Comptes clients',
            'type_document' => 'bilan',
            'type_nature' => 'actif',
            'entreprise_id' => $entreprise->id,
        ]);
        $classeCaisse = ClasseComptable::create([
            'numero' => '5',
            'nom' => 'Trésorerie',
            'type_document' => 'bilan',
            'type_nature' => 'actif',
            'entreprise_id' => $entreprise->id,
        ]);
        $classeVente = ClasseComptable::create([
            'numero' => '7',
            'nom' => 'Produits',
            'type_document' => 'resultat',
            'type_nature' => 'produit',
            'entreprise_id' => $entreprise->id,
        ]);
        $classeVariation = ClasseComptable::create([
            'numero' => '6',
            'nom' => 'Charges',
            'type_document' => 'resultat',
            'type_nature' => 'charge',
            'entreprise_id' => $entreprise->id,
        ]);
        $compteStock = Compte::create([
            'numero' => '3000',
            'nom' => 'Stock de marchandises',
            'type' => 'actif',
            'entreprise_id' => $entreprise->id,
            'classe_comptable_id' => $classeStock->id,
        ]);
        $compteVariation = Compte::create([
            'numero' => '6000',
            'nom' => 'Variation de stock',
            'type' => 'actif',
            'entreprise_id' => $entreprise->id,
            'classe_comptable_id' => $classeVariation->id,
        ]);
        $compteClient = Compte::create([
            'numero' => '4110',
            'nom' => 'Clients',
            'type' => 'actif',
            'entreprise_id' => $entreprise->id,
            'classe_comptable_id' => $classeClient->id,
        ]);
        $compteCaisse = Compte::create([
            'numero' => '5700',
            'nom' => 'Caisse espèces',
            'type' => 'actif',
            'entreprise_id' => $entreprise->id,
            'classe_comptable_id' => $classeCaisse->id,
        ]);
        $compteCarte = Compte::create([
            'numero' => '5710',
            'nom' => 'Caisse carte',
            'type' => 'actif',
            'entreprise_id' => $entreprise->id,
            'classe_comptable_id' => $classeCaisse->id,
        ]);
        $compteOffre = Compte::create([
            'numero' => '6010',
            'nom' => 'Offres accordées',
            'type' => 'actif',
            'entreprise_id' => $entreprise->id,
            'classe_comptable_id' => $classeVariation->id,
        ]);
        $compteRemise = Compte::create([
            'numero' => '6020',
            'nom' => 'Remises accordées',
            'type' => 'actif',
            'entreprise_id' => $entreprise->id,
            'classe_comptable_id' => $classeVariation->id,
        ]);
        $compteVenteBoissons = Compte::create([
            'numero' => '7010',
            'nom' => 'Ventes boissons',
            'type' => 'passif',
            'entreprise_id' => $entreprise->id,
            'classe_comptable_id' => $classeVente->id,
        ]);
        $compteVenteRepas = Compte::create([
            'numero' => '7020',
            'nom' => 'Ventes repas',
            'type' => 'passif',
            'entreprise_id' => $entreprise->id,
            'classe_comptable_id' => $classeVente->id,
        ]);
        $pointDeVente = PointDeVente::create([
            'nom' => 'PDV test',
            'etat' => 'ferme',
            'entreprise_id' => $entreprise->id,
            'compte_client_id' => $compteClient->id,
            'compte_remise_id' => $compteRemise->id,
        ]);
        $categorie = Categorie::create([
            'nom' => 'Produits test',
            'entreprise_id' => $entreprise->id,
            'compte_stock_id' => $compteStock->id,
            'compte_variation_stock_id' => $compteVariation->id,
            'compte_vente_id' => $compteVenteBoissons->id,
        ]);
        $categorieRepas = Categorie::create([
            'nom' => 'Repas',
            'entreprise_id' => $entreprise->id,
            'compte_stock_id' => $compteStock->id,
            'compte_variation_stock_id' => $compteVariation->id,
            'compte_vente_id' => $compteVenteRepas->id,
        ]);

        $produitGratuit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Produit gratuit',
            'prix_achat' => 0,
            'prix_vente' => 10,
        ]);
        $produitPayant = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Produit payant',
            'prix_achat' => 10,
            'prix_vente' => 20,
        ]);
        $produitRepas = Produit::create([
            'categorie_id' => $categorieRepas->id,
            'nom' => 'Repas test',
            'prix_achat' => 4,
            'prix_vente' => 12,
        ]);

        foreach ([[$produitGratuit, 2], [$produitPayant, 4], [$produitRepas, 1]] as [$produit, $quantiteVendue]) {
            StockJournalier::create([
                'produit_id' => $produit->id,
                'point_de_vente_id' => $pointDeVente->id,
                'date' => '2026-10-02',
                'session' => '20261002090000',
                'quantite_initiale' => $quantiteVendue,
                'quantite_vendue' => $quantiteVendue,
                'quantite_reste' => 0,
            ]);
        }

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
        $clientUn = Client::create(['nom' => 'Client Un', 'entreprise_id' => $entreprise->id]);
        $clientDeux = Client::create(['nom' => 'Client Deux', 'entreprise_id' => $entreprise->id]);
        $panierUn = Panier::create([
            'table_id' => $tableId,
            'point_de_vente_id' => $pointDeVente->id,
            'client_id' => $clientUn->id,
            'produits_json' => [],
            'status' => 'validé',
        ]);
        $panierUn->produits()->attach($produitGratuit->id, ['quantite' => 2, 'prix' => 10]);
        $panierUn->produits()->attach($produitPayant->id, ['quantite' => 3, 'prix' => 20]);
        $panierUn->produits()->attach($produitRepas->id, ['quantite' => 1, 'prix' => 12]);
        $panierUn->forceFill(['total_remise' => 5])->save();

        $panierDeux = Panier::create([
            'table_id' => $tableId,
            'point_de_vente_id' => $pointDeVente->id,
            'client_id' => $clientDeux->id,
            'produits_json' => [],
            'status' => 'validé',
        ]);
        $panierDeux->produits()->attach($produitPayant->id, ['quantite' => 1, 'prix' => 20]);
        $panierDeux->forceFill(['total_remise' => 2])->save();

        $commandeUn = Commande::create([
            'panier_id' => $panierUn->id,
            'mode_paiement' => 'compte_client',
            'statut' => 'validé',
            'created_at' => '2026-10-02 10:00:00',
        ]);
        $commandeDeux = Commande::create([
            'panier_id' => $panierDeux->id,
            'mode_paiement' => 'compte_client',
            'statut' => 'validé',
            'created_at' => '2026-10-02 11:00:00',
        ]);

        ModePaiement::create([
            'nom' => 'Espèces',
            'code' => 'especes',
            'actif' => true,
            'est_systeme' => true,
            'ordre' => 10,
            'entreprise_id' => $entreprise->id,
            'compte_id' => $compteCaisse->id,
        ]);
        ModePaiement::create([
            'nom' => 'Offre',
            'code' => 'offre',
            'actif' => true,
            'est_systeme' => true,
            'ordre' => 20,
            'entreprise_id' => $entreprise->id,
            'compte_id' => $compteOffre->id,
        ]);
        ModePaiement::create([
            'nom' => 'Carte',
            'code' => 'carte',
            'actif' => true,
            'est_systeme' => false,
            'ordre' => 30,
            'entreprise_id' => $entreprise->id,
            'compte_id' => $compteCarte->id,
        ]);
        foreach ([[$commandeUn, 'especes', 30], [$commandeDeux, 'especes', 40], [$commandeUn, 'offre', 10], [$commandeDeux, 'carte', 15]] as [$commande, $mode, $montant]) {
            Paiement::create([
                'commande_id' => $commande->id,
                'montant' => $montant,
                'montant_restant' => 0,
                'mode' => $mode,
                'date_paiement' => '2026-10-02',
                'user_id' => $user->id,
                'statut' => 'validé',
            ]);
        }

        $journaux = app(ComptabiliteService::class)->genererBrouillonsFermetureSession(
            $pointDeVente,
            '2026-10-02',
            '20261002090000',
            Carbon::parse('2026-10-02 09:00:00'),
            Carbon::parse('2026-10-02 18:00:00'),
            $user->id
        );

        $journalStock = collect($journaux)->firstWhere('type_operation', 'ajustement');
        $journalVente = collect($journaux)->firstWhere('type_operation', 'vente');
        $journalPaiement = collect($journaux)->firstWhere('type_operation', 'paiement');

        $this->assertNotNull($journalVente);
        $this->assertSame('Vente du 02-10-2026 - PDV test', $journalVente->libelle);
        $lignesVente = $journalVente->ecritures;
        $this->assertSame(2, $lignesVente->where('compte_id', $compteClient->id)->where('debit', '>', 0)->count());
        $this->assertSame(1, $lignesVente->where('compte_id', $compteVenteBoissons->id)->count());
        $this->assertSame(1, $lignesVente->where('compte_id', $compteVenteRepas->id)->count());
        $this->assertEquals(112, $lignesVente->where('compte_id', $compteClient->id)->where('debit', '>', 0)->sum('debit'));
        $this->assertEquals(100, $lignesVente->where('compte_id', $compteVenteBoissons->id)->sum('credit'));
        $this->assertEquals(12, $lignesVente->where('compte_id', $compteVenteRepas->id)->sum('credit'));
        $this->assertSame(0, $lignesVente->where('compte_id', $compteRemise->id)->count());
        $this->assertEquals(0, $lignesVente->where('compte_id', $compteClient->id)->sum('credit'));
        $this->assertSame(1, $lignesVente->where('libelle', 'Vente Produits test du vendredi 02-10-2026')->count());

        $this->assertNotNull($journalPaiement);
        $this->assertSame('Règlement de la session du 02-10-2026', $journalPaiement->libelle);
        $lignesPaiement = $journalPaiement->ecritures;
        $this->assertSame(1, $lignesPaiement->where('compte_id', $compteCaisse->id)->where('debit', '>', 0)->count());
        $this->assertEquals(70, $lignesPaiement->where('compte_id', $compteCaisse->id)->sum('debit'));
        $this->assertSame(1, $lignesPaiement->where('compte_id', $compteOffre->id)->where('libelle', 'Offre du vendredi 02-10-2026')->count());
        $this->assertSame(1, $lignesPaiement->where('compte_id', $compteCaisse->id)->where('libelle', 'Règlement factures clients par Espèces du vendredi 02-10-2026')->count());
        $this->assertSame(1, $lignesPaiement->where('compte_id', $compteCarte->id)->where('libelle', 'Règlement facture par Carte du vendredi 02-10-2026')->count());
        $this->assertSame(1, $lignesPaiement->where('compte_id', $compteRemise->id)->where('libelle', 'Remise du vendredi 02-10-2026')->count());
        $this->assertEquals(7, $lignesPaiement->where('compte_id', $compteRemise->id)->sum('debit'));
        $this->assertEquals(7, $lignesPaiement->where('compte_id', $compteClient->id)->sum('credit')
            - $lignesPaiement->where('compte_id', $compteCaisse->id)->sum('debit')
            - $lignesPaiement->where('compte_id', $compteOffre->id)->sum('debit')
            - $lignesPaiement->where('compte_id', $compteCarte->id)->sum('debit'));

        $lignesStock = $journalStock->ecritures;
        $this->assertSame(2, $lignesStock->where('debit', '>', 0)->count());
        $this->assertSame(2, $lignesStock->where('credit', '>', 0)->count());
        $this->assertSame(1, $lignesStock->where('libelle', 'Variation stock Produits test du vendredi 02-10-2026')->count());
        $this->assertSame(1, $lignesStock->where('libelle', 'Sortie stock Repas du vendredi 02-10-2026')->count());

        $this->assertNotNull($journalStock);
        $this->assertSame('Variation stock du 02-10-2026', $journalStock->libelle);
        $this->assertSame('44.00', $journalStock->fresh()->montant_total);
        $this->assertEquals(44, $journalStock->ecritures()->sum('debit'));
        $this->assertEquals(44, $journalStock->ecritures()->sum('credit'));
        $this->assertSame(1, JournalComptable::where('point_de_vente_id', $pointDeVente->id)
            ->where('type_operation', 'ajustement')
            ->count());

        $panierRemiseSeule = Panier::create([
            'table_id' => $tableId,
            'point_de_vente_id' => $pointDeVente->id,
            'client_id' => $clientUn->id,
            'produits_json' => [],
            'status' => 'validé',
        ]);
        $panierRemiseSeule->produits()->attach($produitPayant->id, ['quantite' => 1, 'prix' => 20]);
        $panierRemiseSeule->forceFill(['total_remise' => 3])->save();
        Commande::create([
            'panier_id' => $panierRemiseSeule->id,
            'mode_paiement' => 'compte_client',
            'statut' => 'validé',
            'created_at' => '2026-10-02 19:00:00',
        ]);

        $journauxRemiseSeule = app(ComptabiliteService::class)->genererBrouillonsFermetureSession(
            $pointDeVente,
            '2026-10-02',
            '20261002180000',
            Carbon::parse('2026-10-02 18:00:00'),
            Carbon::parse('2026-10-02 20:00:00'),
            $user->id
        );

        $journalRemiseSeule = collect($journauxRemiseSeule)->firstWhere('type_operation', 'paiement');
        $this->assertNotNull($journalRemiseSeule);
        $this->assertSame(2, $journalRemiseSeule->ecritures->count());
        $this->assertEquals(3, $journalRemiseSeule->ecritures->where('compte_id', $compteRemise->id)->sum('debit'));
        $this->assertEquals(3, $journalRemiseSeule->ecritures->where('compte_id', $compteClient->id)->sum('credit'));
    }
}