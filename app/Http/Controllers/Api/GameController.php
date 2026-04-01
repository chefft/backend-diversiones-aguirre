<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        // Retorna todos los juegos activos para el catálogo
        $games = Game::where('status', 'active')->get();
        return response()->json($games);
    }

    public function show($slug)
    {
        // Busca un juego específico por su slug (para la vista 3D)
        $game = Game::where('slug', $slug)->firstOrFail();
        return response()->json($game);
    }
}