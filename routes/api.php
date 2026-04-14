<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\ReservationController;

/*
|--------------------------------------------------------------------------
| API Routes - Diversiones Aguirre
|--------------------------------------------------------------------------
*/

// --- Rutas de Catálogo y Juegos ---
// Listar todos los juegos activos
Route::get('/games', [GameController::class, 'index']);
// Ver detalle de un juego por su slug (para el visor 3D)
Route::get('/games/{slug}', [GameController::class, 'show']);

// --- Rutas de Experiencia Inmersiva ---
// Obtener imágenes y puntos de interés 360
Route::get('/gallery-360', [GalleryController::class, 'index']);

// --- Rutas de Transacciones ---
// Crear una nueva reservación (Cálculo de precio automático)
Route::post('/reservations', [ReservationController::class, 'store']);
Route::get('/calendario', [ReservationController::class, 'calendar']);
