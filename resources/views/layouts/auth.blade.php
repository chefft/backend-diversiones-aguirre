<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Diversiones Aguirre</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #1a1033;
            --panel: rgba(30, 18, 58, 0.86);
            --line: rgba(180, 141, 255, 0.3);
            --text: #f2f5ff;
            --muted: #d3c5f3;
            --accent: #8b5cff;
            --danger: #ff4a5f;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Sora", sans-serif;
            color: var(--text);
            background:
                radial-gradient(900px 540px at 8% 0%, rgba(139, 92, 255, 0.32), transparent 55%),
                radial-gradient(900px 580px at 100% 0%, rgba(46, 204, 113, 0.24), transparent 50%),
                radial-gradient(700px 480px at 50% 110%, rgba(255, 74, 95, 0.22), transparent 55%),
                linear-gradient(140deg, #120826, var(--bg));
            display: grid;
            place-items: center;
            padding: 16px;
        }

        .card {
            width: min(460px, 100%);
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--panel);
            backdrop-filter: blur(10px);
            padding: 24px 20px;
            box-shadow: 0 30px 60px rgba(4, 8, 20, 0.6);
        }

        h1 {
            margin: 0;
            font-size: 1.6rem;
        }

        p {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .links {
            margin-top: 14px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .link-chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 6px 10px;
            color: var(--text);
            text-decoration: none;
            font-size: 0.78rem;
        }

        .form {
            margin-top: 20px;
            display: grid;
            gap: 12px;
        }

        label {
            font-size: 0.82rem;
            color: var(--muted);
        }

        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 11px 12px;
            background: rgba(7, 12, 28, 0.86);
            color: var(--text);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: var(--muted);
        }

        .remember input {
            width: auto;
        }

        button {
            border: 1px solid rgba(139, 92, 255, 0.65);
            border-radius: 10px;
            background: linear-gradient(90deg, rgba(139, 92, 255, 0.32), rgba(46, 204, 113, 0.14));
            color: var(--text);
            padding: 11px 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .alert {
            margin-top: 14px;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.85rem;
        }

        .alert.error {
            border: 1px solid rgba(255, 122, 122, 0.45);
            background: rgba(255, 122, 122, 0.17);
            color: #ffd9d9;
        }

        .alert.ok {
            border: 1px solid rgba(79, 255, 212, 0.4);
            background: rgba(79, 255, 212, 0.14);
            color: #dbfff4;
        }

        .hint {
            margin-top: 14px;
            font-size: 0.8rem;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>@yield('heading')</h1>
        <p>@yield('description')</p>

        <div class="links">
            <a class="link-chip" href="{{ route('landing') }}">Ir al portal</a>
            <a class="link-chip" href="{{ route('login.admin') }}">Login admin</a>
            <a class="link-chip" href="{{ route('login.client') }}">Login cliente</a>
        </div>

        @if (session('status'))
            <div class="alert ok">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
