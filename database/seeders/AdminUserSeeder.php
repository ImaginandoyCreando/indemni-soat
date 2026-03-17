<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea el primer usuario administrador.
     *
     * Ejecutar con:  php artisan db:seed --class=AdminUserSeeder
     *
     * Luego cambiar la contraseña desde el panel de usuarios.
     */
    public function run(): void
    {
        // Evitar duplicados si ya existe
        if (User::where('email', 'admin@indemnisoat.com')->exists()) {
            $this->command->info('El usuario admin ya existe. No se creó uno nuevo.');
            return;
        }

        User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@indemnisoat.com',
            'password' => Hash::make('Admin1234!'),
            'role'     => 'admin',
        ]);

        $this->command->info('✅ Usuario admin creado:');
        $this->command->info('   Email:      admin@indemnisoat.com');
        $this->command->info('   Contraseña: Admin1234!');
        $this->command->info('   ⚠️  Cambia la contraseña al iniciar sesión.');
    }
}