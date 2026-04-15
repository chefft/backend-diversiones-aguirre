@extends('layouts.auth')

@section('title', 'Login Admin')
@section('heading', 'Acceso Administrativo')
@section('description', 'Panel de control para gestion de reservas, clientes y operacion.')

@section('content')
    <form class="form" method="POST" action="{{ route('login.admin.attempt') }}">
        @csrf

        <div>
            <label for="email">Correo admin</label>
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

        <button type="submit">Entrar como administrador</button>
    </form>

    <p class="hint">
        Demo: admin@diversionesaguirre.com / admin123
    </p>
@endsection
