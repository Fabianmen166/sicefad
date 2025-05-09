<!DOCTYPE html>
<html lang="es">
<head>
<<<<<<< HEAD
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
=======
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

    .nav-links {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .nav-item {
      list-style: none;
    }

    .active {
      border-bottom: 2px solid #BBDEFB;
    }
  </style>
</head>
<body>

  <!-- Menú con logo del SENA -->
  <div class="navbar">
    <div class="logo-container">
      <img src="https://www.sena.edu.co/Style%20Library/alayout/images/logoSena.png" alt="Logo SENA">
      <a href="/">Inicio</a>
    </div>
    

    <div class="nav-links">
      @if(Auth::check() && checkRol('lscefa.admin'))
      <li class="nav-item">
        <a href="{{ route('lscefa.admin.welcome') }}" class="@if (Route::is('lscefa.admin.*')) active @endif">Administración</a>
      </li>

      @endif



      @if(Auth::check() && checkRol('lscefa.intern'))
      <li class="nav-item">
        <a href="{{ route('lscefa.intern.panelpas') }}" class="@if (Route::is('lscefa.intern.*')) active @endif">Pasante</a>
      </li>

      @endif


      @if(Auth::check() && checkRol('lscefa.technical'))
      <li class="nav-item">
        <a href="{{ route('lscefa.technical.technical') }}" class="@if (Route::is('lscefa.technical.*')) active @endif">Personal Técnico</a>
      </li>
      @endif

    </div>
  </div>


  
  <!-- Contenido principal -->
  <div class="container">
    <h1>LABORATORIO DE CIENCIAS BÁSICAS</h1>
    <h2>LABORATORIO CEFA</h2>
    <p>Tu espacio para crear y descubrir</p>
    <a href="/login" class="btn-login">Iniciar Sesión</a>
  </div>

</body>
</html>
>>>>>>> 0e4ae791 (aaaaaa)
