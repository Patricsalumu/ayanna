<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer une entreprise de test en premier
        $entreprise = \App\Models\Entreprise::firstOrCreate(
            ['nom' => 'Test Company'],
            [
                'email' => 'test@company.com',
                'telephone' => '+1234567890',
                'adresse' => '123 Main St',
                'ville' => 'Test City',
                'pays' => 'Test Country',
            ]
        );

        // Créer un utilisateur de test lié à cette entreprise
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'entreprise_id' => $entreprise->id,
                'password' => bcrypt('password'),
            ]
        );

        // Appeler les autres seeders
        $this->call([
            ClassesComptablesSeeder::class,
            ComptesSeeder::class,
            AssignClassesComptablesToEntreprises::class,
        ]);
    }
}
