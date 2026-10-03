<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointDeVenteNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_navigation_uses_the_authenticated_users_enterprise(): void
    {
        $entrepriseUtilisateur = Entreprise::create([
            'nom' => 'Entreprise utilisateur',
            'email' => 'navigation-utilisateur@ayanna.test',
        ]);
        $autreEntreprise = Entreprise::create([
            'nom' => 'Autre entreprise',
            'email' => 'navigation-autre@ayanna.test',
        ]);
        $user = User::factory()->create([
            'entreprise_id' => $entrepriseUtilisateur->id,
            'role' => 'Administrateur',
        ]);

        $response = $this->actingAs($user)
            ->withHeader('referer', route('pointsDeVente.show', $autreEntreprise->id))
            ->get(route('pointsDeVente.show', $entrepriseUtilisateur->id));

        $response->assertOk()
            ->assertSee('href="' . route('pointsDeVente.show', $entrepriseUtilisateur->id) . '"', false)
            ->assertDontSee('href="' . route('pointsDeVente.show', $autreEntreprise->id) . '"', false);
    }

    public function test_super_admin_can_open_pos_for_another_enterprise_but_regular_user_cannot(): void
    {
        $entrepriseUtilisateur = Entreprise::create([
            'nom' => 'Entreprise utilisateur',
            'email' => 'pos-utilisateur@ayanna.test',
        ]);
        $entrepriseCible = Entreprise::create([
            'nom' => 'Entreprise cible',
            'email' => 'pos-cible@ayanna.test',
        ]);
        $module = Module::create([
            'nom' => 'POS Restaubar',
            'disponible' => true,
        ]);
        $entrepriseCible->modules()->attach($module->id);
        $url = route('pointsDeVente.show', [
            'entreprise' => $entrepriseCible->id,
            'module_id' => $module->id,
        ]);

        $superAdmin = User::factory()->create([
            'entreprise_id' => $entrepriseUtilisateur->id,
            'role' => 'super_admin',
        ]);
        $this->actingAs($superAdmin)->get($url)->assertOk();

        $regularUser = User::factory()->create([
            'entreprise_id' => $entrepriseUtilisateur->id,
            'role' => 'Administrateur',
        ]);
        $this->actingAs($regularUser)
            ->get($url)
            ->assertRedirect(route('pointsDeVente.show', $entrepriseUtilisateur->id))
            ->assertSessionMissing('error');
    }
}
