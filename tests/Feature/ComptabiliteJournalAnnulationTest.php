<?php

namespace Tests\Feature;

use App\Models\ClasseComptable;
use App\Models\Compte;
use App\Models\Entreprise;
use App\Models\EcritureComptable;
use App\Models\JournalComptable;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ComptabiliteJournalAnnulationTest extends TestCase
{
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ecritures_comptables');
        Schema::dropIfExists('journal_comptable');
        Schema::dropIfExists('comptes');
        Schema::dropIfExists('classes_comptables');
        Schema::dropIfExists('users');
        Schema::dropIfExists('entreprises');

        Schema::create('entreprises', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable();
            $table->string('module')->nullable();
            $table->string('telephone')->nullable();
            $table->string('logo')->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('pays')->nullable();
            $table->string('slogan')->nullable();
            $table->string('site_web')->nullable();
            $table->string('identifiant_fiscale')->nullable();
            $table->string('registre_commerce')->nullable();
            $table->string('numero_entreprise')->nullable();
            $table->string('numero_tva')->nullable();
            $table->string('email')->nullable();
            $table->string('devise')->nullable();
            $table->decimal('taux', 15, 2)->nullable();
            $table->timestamps();
        });

        Entreprise::create([
            'id' => 1,
            'nom' => 'Entreprise test',
            'devise' => 'F',
            'taux' => 1,
        ]);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('entreprise_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('classes_comptables', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('type_document')->nullable();
            $table->string('type_nature')->nullable();
            $table->boolean('est_principale')->default(false);
            $table->integer('ordre_affichage')->nullable();
            $table->unsignedBigInteger('entreprise_id')->nullable();
            $table->timestamps();
        });

        Schema::create('comptes', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->string('nom');
            $table->string('type');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('entreprise_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('classe_comptable_id')->nullable();
            $table->decimal('solde_initial', 15, 2)->default(0);
            $table->decimal('solde_debit', 15, 2)->default(0);
            $table->decimal('solde_credit', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('journal_comptable', function (Blueprint $table) {
            $table->id();
            $table->date('date_ecriture');
            $table->time('heure_ecriture');
            $table->string('numero_piece');
            $table->string('libelle');
            $table->decimal('montant_total', 15, 2)->default(0);
            $table->unsignedBigInteger('entreprise_id');
            $table->unsignedBigInteger('point_de_vente_id')->nullable();
            $table->unsignedBigInteger('commande_id')->nullable();
            $table->unsignedBigInteger('panier_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('type_operation')->nullable();
            $table->string('statut')->default('brouillon');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ecritures_comptables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_id');
            $table->unsignedBigInteger('compte_id');
            $table->string('libelle')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('produit_id')->nullable();
            $table->integer('ordre')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function test_un_journal_brouillon_peut_etre_annule_sans_creer_d_ecriture_inverse(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test-user@example.com',
            'password' => Hash::make('password'),
            'entreprise_id' => 1,
        ]);

        $compteDebit = Compte::create([
            'entreprise_id' => 1,
            'numero' => '512',
            'nom' => 'Banque',
            'type' => 'actif',
        ]);

        $compteCredit = Compte::create([
            'entreprise_id' => 1,
            'numero' => '700',
            'nom' => 'Ventes',
            'type' => 'passif',
        ]);

        $journal = JournalComptable::create([
            'date_ecriture' => now()->toDateString(),
            'heure_ecriture' => now()->format('H:i:s'),
            'numero_piece' => 'VEN-20260801-001',
            'libelle' => 'Écriture brouillon',
            'montant_total' => 25000,
            'entreprise_id' => 1,
            'user_id' => $user->id,
            'type_operation' => 'vente',
            'statut' => 'brouillon',
        ]);

        EcritureComptable::create([
            'journal_id' => $journal->id,
            'compte_id' => $compteDebit->id,
            'libelle' => 'Débit brouillon',
            'debit' => 25000,
            'credit' => 0,
            'ordre' => 1,
        ]);

        EcritureComptable::create([
            'journal_id' => $journal->id,
            'compte_id' => $compteCredit->id,
            'libelle' => 'Crédit brouillon',
            'debit' => 0,
            'credit' => 25000,
            'ordre' => 2,
        ]);

        $controller = app(\App\Http\Controllers\ComptabiliteController::class);
        $controller->annulerJournal($journal);

        $journal->refresh();

        $this->assertSame('annule', $journal->statut);
        $this->assertSame(2, $journal->ecritures()->count());
    }

    public function test_les_rapports_excluent_les_ecritures_des_journaux_annules(): void
    {
        $user = User::create([
            'name' => 'Test User Rapport',
            'email' => 'test-user-rapport@example.com',
            'password' => Hash::make('password'),
            'entreprise_id' => 1,
        ]);

        $classeProduit = ClasseComptable::create([
            'numero' => '7',
            'nom' => 'Produits',
            'description' => 'Produits',
            'type_document' => 'resultat',
            'type_nature' => 'produit',
            'est_principale' => true,
            'ordre_affichage' => 1,
            'entreprise_id' => 1,
        ]);

        $classeCharge = ClasseComptable::create([
            'numero' => '6',
            'nom' => 'Charges',
            'description' => 'Charges',
            'type_document' => 'resultat',
            'type_nature' => 'charge',
            'est_principale' => true,
            'ordre_affichage' => 2,
            'entreprise_id' => 1,
        ]);

        $compteProduit = Compte::create([
            'entreprise_id' => 1,
            'numero' => '701',
            'nom' => 'Ventes',
            'type' => 'passif',
            'classe_comptable_id' => $classeProduit->id,
        ]);

        $compteCharge = Compte::create([
            'entreprise_id' => 1,
            'numero' => '601',
            'nom' => 'Achats',
            'type' => 'actif',
            'classe_comptable_id' => $classeCharge->id,
        ]);

        $journalValide = JournalComptable::create([
            'date_ecriture' => '2026-08-01',
            'heure_ecriture' => '10:00:00',
            'numero_piece' => 'VAL-001',
            'libelle' => 'Écriture valide',
            'montant_total' => 1000,
            'entreprise_id' => 1,
            'user_id' => $user->id,
            'type_operation' => 'vente',
            'statut' => 'valide',
        ]);

        EcritureComptable::create([
            'journal_id' => $journalValide->id,
            'compte_id' => $compteProduit->id,
            'libelle' => 'Produit valide',
            'debit' => 0,
            'credit' => 1000,
            'ordre' => 1,
        ]);

        EcritureComptable::create([
            'journal_id' => $journalValide->id,
            'compte_id' => $compteCharge->id,
            'libelle' => 'Charge valide',
            'debit' => 1000,
            'credit' => 0,
            'ordre' => 2,
        ]);

        $journalAnnule = JournalComptable::create([
            'date_ecriture' => '2026-08-01',
            'heure_ecriture' => '11:00:00',
            'numero_piece' => 'ANN-001',
            'libelle' => 'Écriture annulée',
            'montant_total' => 5000,
            'entreprise_id' => 1,
            'user_id' => $user->id,
            'type_operation' => 'vente',
            'statut' => 'annule',
        ]);

        EcritureComptable::create([
            'journal_id' => $journalAnnule->id,
            'compte_id' => $compteProduit->id,
            'libelle' => 'Produit annulé',
            'debit' => 0,
            'credit' => 5000,
            'ordre' => 1,
        ]);

        EcritureComptable::create([
            'journal_id' => $journalAnnule->id,
            'compte_id' => $compteCharge->id,
            'libelle' => 'Charge annulée',
            'debit' => 5000,
            'credit' => 0,
            'ordre' => 2,
        ]);

        $responseCompteResultat = $this->actingAs($user)
            ->get(route('comptabilite.compte-resultat', [
                'date_debut' => '2026-08-01',
                'date_fin' => '2026-08-31',
            ]));

        $responseCompteResultat->assertOk();
        $responseCompteResultat->assertViewHas('totalProduits', 1000);
        $responseCompteResultat->assertViewHas('totalCharges', 1000);

        $responseBilan = $this->actingAs($user)
            ->get(route('comptabilite.bilan', ['date' => '2026-08-31']));

        $responseBilan->assertOk();
        $responseBilan->assertViewHas('totalActif', 1000);
        $responseBilan->assertViewHas('totalPassif', 1000);

        $responseGrandLivre = $this->actingAs($user)
            ->get(route('comptabilite.grand-livre', [
                'compteId' => $compteProduit->id,
                'date_debut' => '2026-08-01',
                'date_fin' => '2026-08-31',
            ]));

        $responseGrandLivre->assertOk();
        $responseGrandLivre->assertViewHas('ecritures', function ($ecritures) {
            return $ecritures->count() === 1;
        });
    }
}
