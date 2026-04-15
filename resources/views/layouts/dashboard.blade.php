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
            --line: rgba(176, 138, 255, 0.24);
            --text: #f5f8ff;
            --muted: #d3c5f3;
            --accent: #8b5cff;
            --warn: #36d27f;
            --danger: #ff4a5f;
            --ok: #8effc8;
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
                radial-gradient(1100px 620px at 0% 0%, rgba(139, 92, 255, 0.26), transparent 55%),
                radial-gradient(1000px 680px at 100% 0%, rgba(46, 204, 113, 0.2), transparent 55%),
                radial-gradient(900px 600px at 50% 115%, rgba(255, 74, 95, 0.2), transparent 55%),
                linear-gradient(140deg, #120826, var(--bg));
        }

        .shell {
            max-width: 1200px;
            margin: 0 auto;
            padding: 22px 16px 30px;
        }

        .topbar {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--panel);
            backdrop-filter: blur(10px);
            padding: 14px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .title {
            margin: 0;
            font-size: 1.2rem;
        }

        .subtitle {
            margin: 6px 0 0;
            font-size: 0.86rem;
            color: var(--muted);
        }

        .nav {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .chip {
            text-decoration: none;
            color: var(--text);
            border: 1px solid var(--line);
            border-radius: 999px;
            font-size: 0.78rem;
            padding: 7px 11px;
            background: rgba(9, 14, 31, 0.7);
        }

        .logout-btn {
            border: 1px solid rgba(255, 138, 138, 0.5);
            border-radius: 999px;
            background: rgba(255, 138, 138, 0.16);
            color: #ffe4e4;
            font-size: 0.78rem;
            padding: 7px 11px;
            cursor: pointer;
        }

        .alerts {
            margin-top: 12px;
            display: grid;
            gap: 8px;
        }

        .alert {
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.84rem;
        }

        .alert.error {
            border: 1px solid rgba(255, 138, 138, 0.45);
            background: rgba(255, 138, 138, 0.16);
            color: #ffdede;
        }

        .alert.status {
            border: 1px solid rgba(140, 255, 203, 0.4);
            background: rgba(140, 255, 203, 0.14);
            color: #d9fff0;
        }

        .grid {
            margin-top: 16px;
            display: grid;
            gap: 12px;
        }

        .cards {
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--panel);
            padding: 14px;
        }

        .label {
            font-size: 0.76rem;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .value {
            font-size: 1.34rem;
            font-weight: 700;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th, td {
            text-align: left;
            border-bottom: 1px solid rgba(129, 149, 194, 0.2);
            padding: 10px 6px;
            font-size: 0.84rem;
        }

        th {
            color: var(--muted);
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 0.72rem;
            border: 1px solid var(--line);
            background: rgba(8, 13, 30, 0.78);
        }

        .badge.pending {
            color: #ffe8c9;
            border-color: rgba(255, 207, 135, 0.5);
            background: rgba(255, 207, 135, 0.17);
        }

        .badge.confirmed {
            color: #d8ffe3;
            border-color: rgba(140, 255, 203, 0.5);
            background: rgba(140, 255, 203, 0.15);
        }

        .badge.completed {
            color: #d6e8ff;
            border-color: rgba(151, 182, 255, 0.5);
            background: rgba(151, 182, 255, 0.16);
        }

        .badge.cancelled {
            color: #ffdede;
            border-color: rgba(255, 138, 138, 0.5);
            background: rgba(255, 138, 138, 0.16);
        }

        .badge.active {
            color: #d8ffe3;
            border-color: rgba(140, 255, 203, 0.5);
            background: rgba(140, 255, 203, 0.15);
        }

        .badge.maintenance {
            color: #eed9ff;
            border-color: rgba(182, 124, 255, 0.5);
            background: rgba(182, 124, 255, 0.16);
        }

        .badge.inactive {
            color: #ffdede;
            border-color: rgba(255, 138, 138, 0.5);
            background: rgba(255, 138, 138, 0.16);
        }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <div>
                <h1 class="title">@yield('heading')</h1>
                <p class="subtitle">@yield('description')</p>
            </div>
            <div class="nav">
                <a class="chip" href="{{ route('landing') }}">Portal</a>
                @yield('nav-links')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">Cerrar sesion</button>
                </form>
            </div>
        </header>

        <div class="alerts">
            @if (session('error'))
                <div class="alert error">{{ session('error') }}</div>
            @endif
            @if (session('status'))
                <div class="alert status">{{ session('status') }}</div>
            @endif
        </div>

        @yield('content')
    </div>
</body>
</html>
