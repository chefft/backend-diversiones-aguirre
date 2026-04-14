<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Gallery360;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Aguirre',
            'email' => 'admin@diversionesaguirre.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Juan Perez',
            'email' => 'juan@gmail.com',
            'password' => Hash::make('cliente123'),
            'role' => 'client',
        ]);

        Game::create([
            'name' => 'Rueda de la Fortuna',
            'slug' => 'rueda-fortuna',
            'description' => 'Clasica atraccion con vista panoramica de toda la feria.',
            'price_per_hour' => 1500.00,
            'model_3d_path' => 'models/rueda.glb',
            'status' => 'active',
        ]);

        Game::create([
            'name' => 'Carrusel Magico',
            'slug' => 'carrusel-magico',
            'description' => 'Caballos tallados a mano con luces LED ritmicas.',
            'price_per_hour' => 1200.00,
            'model_3d_path' => 'models/carrusel.glb',
            'status' => 'active',
        ]);

        Game::create([
            'name' => 'Martillo Extremo',
            'slug' => 'martillo-extremo',
            'description' => 'Atraccion de alta adrenalina con giros de 360 grados.',
            'price_per_hour' => 2500.00,
            'model_3d_path' => 'models/martillo.glb',
            'status' => 'maintenance',
        ]);

        Gallery360::create([
            'title' => 'Plaza Central de Feria',
            'image_path' => 'panoramas/plaza-central.jpg',
            'hotspots' => [
                ['label' => 'Rueda', 'yaw' => 45, 'pitch' => 4],
                ['label' => 'Escenario', 'yaw' => -30, 'pitch' => 2],
            ],
            'is_active' => true,
        ]);

        Gallery360::create([
            'title' => 'Zona Nocturna',
            'image_path' => 'panoramas/zona-nocturna.jpg',
            'hotspots' => [
                ['label' => 'Food Court', 'yaw' => 10, 'pitch' => -3],
            ],
            'is_active' => true,
        ]);
    }
}
