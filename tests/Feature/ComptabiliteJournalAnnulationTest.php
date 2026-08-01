<?php

namespace Tests\Feature;

use App\Models\Compte;
use App\Models\EcritureComptable;
use App\Models\JournalComptable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComptabiliteJournalAnnulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_journal_brouillon_peut_etre_annule_sans_creer_d_ecriture_inverse(): void
    {
        $user = User::factory()->create([
            'entreprise_id' => 1,
        ]);

        $compteDebit = Compte::factory()->create([
            'entreprise_id' => 1,
            'numero' => '512',
            'nom' => 'Banque',
            'type' => 'actif',
        ]);

        $compteCredit = Compte::factory()->create([
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

        $response = $this->actingAs($user)
            ->patch(route('comptabilite.journal.annuler', $journal));

        $response->assertRedirect();
        $journal->refresh();

        $this->assertSame('annule', $journal->statut);
        $this->assertSame(2, $journal->ecritures()->count());
    }
}
