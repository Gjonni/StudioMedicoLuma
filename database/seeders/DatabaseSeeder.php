<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        // Applicazione multi-utente con permessi per modulo: nessuna
        // registrazione pubblica, l'amministratore viene creato qui.
        // Credenziali via .env, sovrascrivibili rilanciando il seeder.
        $admin = User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'gfilice@studiomedicoluma.it')],
            [
                'name' => env('ADMIN_NAME', 'gfilice'),
                'password' => bcrypt(env('ADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);
    }
}
