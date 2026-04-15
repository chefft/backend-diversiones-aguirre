@extends('layouts.dashboard')

@section('title', 'Dashboard Cliente')
@section('heading', 'Mi Panel de Cliente')
@section('description', 'Controla tus proximas reservas y revisa historial.')

@section('nav-links')
    <a class="chip" href="{{ route('client.dashboard') }}">Mi panel</a>
    <a class="chip" href="{{ route('landing') }}">Portal inmersivo</a>
@endsection

@section('content')
    <section class="grid">
        <article class="card">
            <div class="label">Proximas reservas</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Juego</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingReservations as $reservation)
                            <tr>
                                <td>{{ $reservation->game?->name ?? 'Sin juego' }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->start_date)->format('Y-m-d H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->end_date)->format('Y-m-d H:i') }}</td>
                                <td><span class="badge {{ $reservation->status }}">{{ $reservation->status }}</span></td>
                                <td>${{ number_format((float) $reservation->total_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No tienes reservas proximas. Ve al portal y agenda una experiencia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="grid">
        <article class="card">
            <div class="label">Historial reciente</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Juego</th>
                            <th>Inicio</th>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historyReservations as $reservation)
                            <tr>
                                <td>{{ $reservation->game?->name ?? 'Sin juego' }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->start_date)->format('Y-m-d H:i') }}</td>
                                <td><span class="badge {{ $reservation->status }}">{{ $reservation->status }}</span></td>
                                <td>${{ number_format((float) $reservation->total_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Aun no hay historial de reservas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
