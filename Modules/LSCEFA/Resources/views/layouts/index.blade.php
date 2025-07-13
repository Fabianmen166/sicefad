<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio de Ciencias Básicas</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #e3f2fd, #bbdefb);
            color: #333;
        }

        .navbar {
            background-color: #1565C0;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            animation: slideDown 1s ease;
        }

        @keyframes slideDown {
            from {
                top: -70px;
                opacity: 0;
            }
            to {
                top: 0;
                opacity: 1;
            }
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .navbar a:hover {
            color: #E3F2FD;
        }

        .navbar .logo-container {
            display: flex;
            align-items: center;
            animation: pulse 2.5s infinite;
        }

        .navbar img {
            width: 50px;
            margin-right: 10px;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }

        .container {
            text-align: center;
            padding: 80px 20px;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            color: #0D47A1;
            font-size: 36px;
            margin-bottom: 10px;
        }

        h2 {
            color: #1976D2;
            font-size: 26px;
            margin-bottom: 15px;
        }

        p {
            font-size: 18px;
            color: #555;
            margin-bottom: 30px;
        }

        .btn-login {
            background-color: #1E88E5;
            color: white;
            padding: 12px 30px;
            border: none;
            font-size: 18px;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .btn-login:hover {
            background-color: #0D47A1;
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <!-- Menú con logo del SENA -->
    <div class="navbar">
    <div class="logo-container">
        <img src="https://www.soydebuenaventura.com/media/transparent/20230802_logosena.png" width="100px">
        <a href="/">Inicio</a>
    </div>

    @if(isset($roles))
        @php
            $roles_lower = array_map('strtolower', $roles);
        @endphp
        {{-- <pre style="color:black;background:#fff;padding:10px;z-index:999;position:absolute;top:60px;right:10px;max-width:400px;">Roles: {{ print_r($roles, true) }}</pre> --}}
        @if(in_array('lscefa.admin', $roles_lower))
            <a href="{{ route('lscefa.admin.welcome') }}" class="btn-login" style="margin-left:auto;">Ir al Dashboard de Administración</a>
        @elseif(in_array('lscefa.quality', $roles_lower))
            <a href="{{ route('lscefa.quality.dashboard') }}" class="btn-login" style="margin-left:auto; background-color:#43a047;">Ir al Dashboard de Calidad</a>
        @elseif(in_array('lscefa.intern', $roles_lower))
            <a href="{{ route('lscefa.intern.panelpas') }}" class="btn-login" style="margin-left:auto; background-color:#0288d1;">Ir al Panel de Pasante</a>
        @elseif(in_array('lscefa.technical', $roles_lower))
            <a href="{{ route('lscefa.technical.panel') }}" class="btn-login" style="margin-left:auto; background-color:#fbc02d; color:#333;">Ir al Panel Técnico</a>
        @endif
    @endif
    </div>


    <!-- Contenido principal -->
    <div class="container">
        <h1>LABORATORIO DE CIENCIAS BÁSICAS</h1>
        <h2>LABORATORIO CEFA</h2>
        <p>Tu espacio para crear y descubrir</p>
        @if (!auth()->check())
            <a href="{{ route('login') }}" class="btn-login">Iniciar Sesión</a>
        @endif
    </div>
</body>

</html>
