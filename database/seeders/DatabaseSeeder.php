<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Game;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Usuario Administrador
        User::create([
            'name' => 'Admin Aguirre',
            'email' => 'admin@diversionesaguirre.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // 2. Crear Usuario Cliente de prueba
        User::create([
            'name' => 'Juan Perez',
            'email' => 'juan@gmail.com',
            'password' => Hash::make('cliente123'),
            'role' => 'client',
        ]);

        // 3. Crear Juegos Mecánicos de prueba
        Game::create([
            'name' => 'Rueda de la Fortuna',
            'slug' => 'rueda-fortuna',
            'description' => 'Clásica atracción con vista panorámica de toda la feria.',
            'price_per_hour' => 1500.00,
            'model_3d_path' => 'models/rueda.glb',
            'status' => 'active',
        ]);

        Game::create([
            'name' => 'Carrusel Mágico',
            'slug' => 'carrusel-magico',
            'description' => 'Caballos tallados a mano con luces LED rítmicas.',
            'price_per_hour' => 1200.00,
            'model_3d_path' => 'models/carrusel.glb',
            'status' => 'active',
        ]);

        Game::create([
            'name' => 'Martillo Extremo',
            'slug' => 'martillo-extremo',
            'description' => 'Atracción de alta adrenalina con giros de 360 grados.',
            'price_per_hour' => 2500.00,
            'model_3d_path' => 'models/martillo.glb',
            'status' => 'maintenance',
        ]);
    }
}