<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        $upcomingReservations = Reservation::query()
            ->with('game:id,name')
            ->where('user_id', $user->id)
            ->where('start_date', '>=', Carbon::now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_date')
            ->take(6)
            ->get();

        $historyReservations = Reservation::query()
            ->with('game:id,name')
            ->where('user_id', $user->id)
            ->orderByDesc('start_date')
            ->take(6)
            ->get();

        return view('client.dashboard', [
            'upcomingReservations' => $upcomingReservations,
            'historyReservations' => $historyReservations,
        ]);
    }
}
