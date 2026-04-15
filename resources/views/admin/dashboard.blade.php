@extends('layouts.dashboard')

@section('title', 'Dashboard Admin')
@section('heading', 'Panel Administrativo')
@section('description', 'Vista general para controlar clientes, modelos y reservas en un solo lugar.')

@section('nav-links')
    <a class="chip" href="{{ route('admin.dashboard') }}">Panel general</a>
    <a class="chip" href="{{ route('landing') }}">Vista publica</a>
@endsection

@section('content')
    <section class="grid cards">
        <article class="card">
            <div class="label">Modelos activos</div>
            <div class="value">{{ $stats['games_active'] }}</div>
        </article>
        <article class="card">
            <div class="label">Modelos totales</div>
            <div class="value">{{ $stats['games_total'] }}</div>
        </article>
        <article class="card">
            <div class="label">Clientes</div>
            <div class="value">{{ $stats['clients_total'] }}</div>
        </article>
        <article class="card">
            <div class="label">Administradores</div>
            <div class="value">{{ $stats['admins_total'] }}</div>
        </article>
        <article class="card">
            <div class="label">Reservas abiertas</div>
            <div class="value">{{ $stats['reservations_open'] }}</div>
        </article>
        <article class="card">
            <div class="label">Reservas de hoy</div>
            <div class="value">{{ $stats['reservations_today'] }}</div>
        </article>
        <article class="card">
            <div class="label">Ingreso pendiente</div>
            <div class="value">${{ number_format((float) $stats['revenue_pending'], 2) }}</div>
        </article>
    </section>

    <section class="grid">
        <article class="card">
            <div class="label">Reservas recientes</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Modelo</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReservations as $reservation)
                            <tr>
                                <td>{{ $reservation->user?->name ?? 'Sin cliente' }}</td>
                                <td>{{ $reservation->game?->name ?? 'Sin modelo' }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->start_date)->format('Y-m-d H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->end_date)->format('Y-m-d H:i') }}</td>
                                <td>${{ number_format((float) $reservation->total_price, 2) }}</td>
                                <td><span class="badge {{ $reservation->status }}">{{ $reservation->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No hay reservas registradas todavia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="grid">
        <article class="card">
            <div class="label">Clientes recientes</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Alta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->email }}</td>
                                <td>{{ \Carbon\Carbon::parse($client->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">No hay clientes disponibles.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="grid">
        <article class="card">
            <div class="label">Modelos y estado actual</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Modelo</th>
                            <th>Estado</th>
                            <th>Precio / hora</th>
                            <th>Ultimo cambio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($games as $game)
                            <tr>
                                <td>{{ $game->name }}</td>
                                <td><span class="badge {{ $game->status }}">{{ $game->status }}</span></td>
                                <td>${{ number_format((float) $game->price_per_hour, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($game->updated_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No hay modelos configurados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
