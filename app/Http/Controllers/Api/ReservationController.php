<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
        ]);

        $game = Game::findOrFail($validated['game_id']);
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);

        $hasOverlap = Reservation::query()
            ->where('game_id', $game->id)
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '<', $end)
            ->where('end_date', '>', $start)
            ->exists();

        if ($hasOverlap) {
            return response()->json([
                'message' => 'El horario seleccionado ya esta ocupado',
            ], 422);
        }

        $hours = max(1, $start->diffInHours($end));

        $reservation = Reservation::create([
            'user_id' => 2,
            'game_id' => $game->id,
            'start_date' => $start,
            'end_date' => $end,
            'total_price' => $hours * $game->price_per_hour,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Reserva creada con exito',
            'reservation' => $reservation,
        ], 201);
    }

    public function calendar(Request $request)
    {
        $validated = $request->validate([
            'inicio' => 'required|date',
            'fin' => 'required|date|after_or_equal:inicio',
            'game_id' => 'nullable|exists:games,id',
            'intervalo' => 'nullable|integer|min:15|max:240',
        ]);

        $timezone = config('calendar.timezone', config('app.timezone', 'UTC'));
        $intervalMinutes = (int) ($validated['intervalo'] ?? config('calendar.slot_minutes', 60));

        $from = Carbon::parse($validated['inicio'], $timezone)->startOfDay();
        $to = Carbon::parse($validated['fin'], $timezone)->endOfDay();

        $availabilityStart = config('calendar.availability_start', '10:00');
        $availabilityEnd = config('calendar.availability_end', '18:00');
        $dayWindowStart = config('calendar.day_window_start', '08:00');
        $dayWindowEnd = config('calendar.day_window_end', '20:00');

        $reservationsQuery = Reservation::query()
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '<', $to)
            ->where('end_date', '>', $from);

        if (!empty($validated['game_id'])) {
            $reservationsQuery->where('game_id', $validated['game_id']);
        }

        $reservationRanges = $reservationsQuery->get(['start_date', 'end_date'])->map(
            function (Reservation $reservation) use ($timezone) {
                return [
                    'start' => Carbon::parse($reservation->start_date, $timezone),
                    'end' => Carbon::parse($reservation->end_date, $timezone),
                ];
            }
        );

        $calendar = [];
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $dayStart = $day->copy()->setTimeFromTimeString($dayWindowStart);
            $dayEnd = $day->copy()->setTimeFromTimeString($dayWindowEnd);
            $availableFrom = $day->copy()->setTimeFromTimeString($availabilityStart);
            $availableTo = $day->copy()->setTimeFromTimeString($availabilityEnd);

            if ($dayEnd->lte($dayStart)) {
                $dayEnd = $dayStart->copy()->addDay();
            }

            if ($availableTo->lte($availableFrom)) {
                $availableTo = $availableFrom->copy();
            }

            $blocks = [];
            for ($blockStart = $dayStart->copy(); $blockStart->lt($dayEnd); $blockStart->addMinutes($intervalMinutes)) {
                $blockEnd = $blockStart->copy()->addMinutes($intervalMinutes);
                if ($blockEnd->gt($dayEnd)) {
                    $blockEnd = $dayEnd->copy();
                }

                $isInsideAvailability = $blockStart->gte($availableFrom) && $blockEnd->lte($availableTo);
                $state = 'no_disponible';

                if ($isInsideAvailability) {
                    $isOccupied = $reservationRanges->contains(function (array $range) use ($blockStart, $blockEnd) {
                        return $range['start']->lt($blockEnd) && $range['end']->gt($blockStart);
                    });

                    $state = $isOccupied ? 'ocupado' : 'disponible';
                }

                $blocks[] = [
                    'inicio' => $blockStart->format('H:i'),
                    'fin' => $blockEnd->format('H:i'),
                    'estado' => $state,
                ];
            }

            $calendar[] = [
                'fecha' => $day->format('Y-m-d'),
                'bloques' => $blocks,
            ];
        }

        return response()->json($calendar);
    }
}
