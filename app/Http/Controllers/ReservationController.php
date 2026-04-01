<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Game;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validar la entrada
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
        ]);

        $game = Game::find($request->game_id);
        
        // 2. Calcular duración en horas
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $hours = $start->diffInHours($end);
        
        // Si la reserva es de menos de una hora, cobramos la hora completa
        $hours = $hours == 0 ? 1 : $hours;

        // 3. Crear la reserva
        $reservation = Reservation::create([
            'user_id' => 2, // Por ahora usamos el ID del cliente que creamos en el Seeder
            'game_id' => $request->game_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_price' => $hours * $game->price_per_hour,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Reserva creada con éxito',
            'reservation' => $reservation
        ], 201);
    }
}