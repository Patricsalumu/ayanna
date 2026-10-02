<?php

namespace Tests\Unit;

use App\Models\Compte;
use App\Models\Entreprise;
use App\Models\EntreeSortie;
use App\Models\PointDeVente;
use App\Models\User;
use App\Services\ComptabiliteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComptabiliteMouvementTest extends TestCase
{
    use RefreshDatabase;

    public function test_entry_debits_configured_cash_and_credits_selected_account_while_exit_stays_unchanged(): void
    {
        $entreprise = Entreprise::create([
            'nom' => 'Ayanna',
            'email' => 'mouvement-comptable@ayanna.test',
            'telephone' => '0000000000',
            'adresse' => 'Adresse test',
            'devise' => 'XAF',
        ]);
        $user = User::factory()->create(['entreprise_id' => $entreprise->id]);
        $compteCaisse = Compte::create([
            'numero' => '5701',
            'nom' => 'Caisse PDV',
            'type' => 'actif',
            'entreprise_id' => $entreprise->id,
        ]);
        $compteSelectionne = Compte::create([
            'numero' => '7580',
            'nom' => 'Autres produits',
            'type' => 'passif',
            'entreprise_id' => $entreprise->id,
        ]);
        $pointDeVente = PointDeVente::create([
            'nom' => 'PDV test',
            'etat' => 'ouvert',
            'entreprise_id' => $entreprise->id,
            'compte_caisse_id' => $compteCaisse->id,
        ]);

        $service = app(ComptabiliteService::class);
        $entree = EntreeSortie::create([
            'compte_id' => $compteSelectionne->id,
            'montant' => 1250,
            'libele' => 'Apport de caisse',
            'type' => 'entree',
            'user_id' => $user->id,
            'point_de_vente_id' => $pointDeVente->id,
        ]);
        $journalEntree = $service->enregistrerMouvement($entree);
        $ecrituresEntree = $journalEntree->ecritures()->get();

        $this->assertSame('brouillon', $journalEntree->statut);
        $this->assertCount(2, $ecrituresEntree);
        $this->assertSame(1250.0, (float) $ecrituresEntree->firstWhere('compte_id', $compteCaisse->id)->debit);
        $this->assertSame(0.0, (float) $ecrituresEntree->firstWhere('compte_id', $compteCaisse->id)->credit);
        $this->assertSame(0.0, (float) $ecrituresEntree->firstWhere('compte_id', $compteSelectionne->id)->debit);
        $this->assertSame(1250.0, (float) $ecrituresEntree->firstWhere('compte_id', $compteSelectionne->id)->credit);

        $sortie = EntreeSortie::create([
            'compte_id' => $compteSelectionne->id,
            'montant' => 300,
            'libele' => 'Dépense test',
            'type' => 'sortie',
            'user_id' => $user->id,
            'point_de_vente_id' => $pointDeVente->id,
        ]);
        $journalSortie = $service->enregistrerMouvement($sortie);
        $ecrituresSortie = $journalSortie->ecritures()->get();

        $this->assertSame('brouillon', $journalSortie->statut);
        $this->assertCount(2, $ecrituresSortie);
        $this->assertSame(300.0, (float) $ecrituresSortie->firstWhere('compte_id', $compteSelectionne->id)->debit);
        $this->assertSame(300.0, (float) $ecrituresSortie->firstWhere('compte_id', $compteCaisse->id)->credit);
    }
}
