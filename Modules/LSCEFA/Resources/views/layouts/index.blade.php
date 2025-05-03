<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio de Ciencias Básicas</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar {
            background-color: #1976D2;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            font-weight: 500;
        }

        .navbar .right {
            display: flex;
            align-items: center;
        }

        .navbar form {
            margin: 0;
        }

        .container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            padding: 20px;
        }

        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 80px;
        }

        h1 {
            color: #1976D2;
            font-size: 28px;
            margin-bottom: 10px;
        }

        h2 {
            color: #333;
            font-size: 22px;
            margin-bottom: 10px;
        }

        p {
            color: #555;
            font-size: 18px;
            margin-bottom: 30px;
        }

        .btn-login {
            background-color: #2E7D32;
            color: #fff;
            padding: 12px 25px;
            font-size: 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-decoration: none;
            display: inline-block;
        }

        .btn-login:hover {
            background-color: #1B5E20;
        }

        .role-link {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
            text-decoration: underline;
        }

        .button-group {
            display: flex;
            align-items: center;
            gap: 15px;
        }
    </style>
</head>
<body>

    {{-- Menú de navegación --}}
    <div class="navbar">
        <div>
            <a href="/">Inicio</a>
        </div>
        <div class="right">
            @if(Auth::check())
                @php
                    $user = Auth::user();
                    $rolTexto = '';

                    if ($user->role === 'lscefa.admin') {
                        $rolTexto = 'Administrador';
                    } elseif ($user->role === 'lscefa.intern') {
                        $rolTexto = 'Pasante';
                    }
                @endphp
                <span>Rol: {{ $rolTexto }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="role-link">Cerrar Sesión</button>
                </form>
            @else
                <a href="{{ route('login') }}">Iniciar Sesión</a>
            @endif
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="container">
        <h1>LABORATORIO DE CIENCIAS BÁSICAS</h1>
        <h2>LABORATORIO CEFA</h2>
        <p>Tu espacio para crear y descubrir</p>

        <div class="button-group">
            @if(Auth::check())
                @if($user->role === 'lscefa.admin')
                    <a href="{{ route('lscefa.admin.welcome') }}" class="btn-login">Panel de Administración</a>
                @elseif($user->role === 'lscefa.intern')
                    <a href="{{ route('lscefa.intern.panelpas') }}" class="btn-login">Panel de Pasante</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-login">Iniciar Sesión</a>
            @endif
        </div>
    </div>

</body>
</html>