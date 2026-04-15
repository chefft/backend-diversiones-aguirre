<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'games_active' => Game::where('status', 'active')->count(),
            'games_total' => Game::count(),
            'clients_total' => User::where('role', 'client')->count(),
            'admins_total' => User::where('role', 'admin')->count(),
            'reservations_open' => Reservation::whereIn('status', ['pending', 'confirmed'])->count(),
            'revenue_pending' => Reservation::whereIn('status', ['pending', 'confirmed'])->sum('total_price'),
            'reservations_today' => Reservation::whereDate('start_date', Carbon::today())->count(),
        ];

        $recentReservations = Reservation::query()
            ->with(['user:id,name,email', 'game:id,name'])
            ->latest()
            ->take(12)
            ->get();

        $clients = User::query()
            ->where('role', 'client')
            ->select(['id', 'name', 'email', 'created_at'])
            ->latest()
            ->take(12)
            ->get();

        $games = Game::query()
            ->select(['id', 'name', 'status', 'price_per_hour', 'updated_at'])
            ->orderByDesc('updated_at')
            ->take(12)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentReservations' => $recentReservations,
            'clients' => $clients,
            'games' => $games,
        ]);
    }
}
