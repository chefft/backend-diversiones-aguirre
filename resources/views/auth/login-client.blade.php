@extends('layouts.auth')

@section('title', 'Login Cliente')
@section('heading', 'Acceso de Cliente')
@section('description', 'Consulta tus reservas, agenda nuevos horarios y revisa tu historial.')

@section('content')
    <form class="form" method="POST" action="{{ route('login.client.attempt') }}">
        @csrf

        <div>
            <label for="email">Correo cliente</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div>
            <label for="password">Contrasena</label>
            <input id="password" name="password" type="password" required>
        </div>

        <label class="remember">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
            Recordarme
        </label>

        <button type="submit">Entrar como cliente</button>
    </form>

    <p class="hint">
        Demo: juan@gmail.com / cliente123
    </p>
@endsection
