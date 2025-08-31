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

        .role-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
            max-width: 400px;
            margin: 0 auto;
        }

        .btn-role {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            min-width: 280px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-role:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .role-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-name {
            font-size: 14px;
            opacity: 0.9;
        }

        .btn-admin {
            background: linear-gradient(135deg, #D32F2F, #F44336);
            color: white;
        }

        .btn-admin:hover {
            background: linear-gradient(135deg, #B71C1C, #D32F2F);
        }

        .btn-quality {
            background: linear-gradient(135deg, #388E3C, #4CAF50);
            color: white;
        }

        .btn-quality:hover {
            background: linear-gradient(135deg, #2E7D32, #388E3C);
        }

        .btn-technical {
            background: linear-gradient(135deg, #1976D2, #2196F3);
            color: white;
        }

        .btn-technical:hover {
            background: linear-gradient(135deg, #1565C0, #1976D2);
        }

        .btn-intern {
            background: linear-gradient(135deg, #FF9800, #FFB74D);
            color: white;
        }

        .btn-intern:hover {
            background: linear-gradient(135deg, #F57C00, #FF9800);
        }

        .help-button {
            background-color: #FF9800;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .help-button:hover {
            background-color: #F57C00;
            transform: scale(1.05);
            color: white;
            text-decoration: none;
        }

        .help-button i {
            font-size: 16px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
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

        <div class="navbar-right">
            @if(Auth::check() && checkRol('lscefa.admin'))
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('lscefa.admin.welcome') }}" class="nav-link @if (Route::is('lscefa.admin.*')) active @endif">Administración</a>
                </li>
            @endif
            @if(Auth::check() && checkRol('lscefa.intern'))
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('lscefa.intern.panelpas') }}" class="nav-link @if (Route::is('lscefa.intern.*')) active @endif">Pasante</a>
                </li>
            @endif
            @if(Auth::check() && checkRol('lscefa.technical'))
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('lscefa.technical.panel') }}" class="nav-link @if (Route::is('lscefa.technical.*')) active @endif">Personal Técnico</a>
                </li>
            @endif
            @if(Auth::check() && checkRol('lscefa.quality'))
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('lscefa.quality.dashboard') }}" class="nav-link @if (Route::is('lscefa.quality.*')) active @endif">Gestión de Calidad</a>
                </li>
            @endif
            
            <a href="#" class="help-button" onclick="downloadManual()">
                <i>📄</i>
                Manual Técnico
            </a>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="container">
        <h1>LABORATORIO DE CIENCIAS BÁSICAS</h1>
        <h2>LABORATORIO CEFA</h2>
        <p>Tu espacio para crear y descubrir</p>
        
        @if (!auth()->check())
            <a href="{{ route('login') }}" class="btn-login">Iniciar Sesión</a>
        @else
            <div class="role-buttons">
                @if(checkRol('lscefa.admin'))
                    <a href="{{ route('lscefa.admin.welcome') }}" class="btn-role btn-admin">
                        <span class="role-title">Administrador</span>
                        <span class="user-name">{{ auth()->user()->name }}</span>
                    </a>
                @endif
                
                @if(checkRol('lscefa.quality'))
                    <a href="{{ route('lscefa.quality.dashboard') }}" class="btn-role btn-quality">
                        <span class="role-title">Gestión de Calidad</span>
                        <span class="user-name">{{ auth()->user()->name }}</span>
                    </a>
                @endif
                
                @if(checkRol('lscefa.technical'))
                    <a href="{{ route('lscefa.technical.panel') }}" class="btn-role btn-technical">
                        <span class="role-title">Personal Técnico</span>
                        <span class="user-name">{{ auth()->user()->name }}</span>
                    </a>
                @endif
                
                @if(checkRol('lscefa.intern'))
                    <a href="{{ route('lscefa.intern.panelpas') }}" class="btn-role btn-intern">
                        <span class="role-title">Pasante</span>
                        <span class="user-name">{{ auth()->user()->name }}</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

    <script>
        function downloadManual() {
            // Crear un enlace temporal para descargar el PDF
            const link = document.createElement('a');
            link.href = '/modules/lscefa/Manual Tecnico.pdf';
            link.download = 'Manual Tecnico.pdf';
            link.target = '_blank';
            
            // Agregar el enlace al DOM y hacer clic
            document.body.appendChild(link);
            link.click();
            
            // Remover el enlace temporal
            document.body.removeChild(link);
        }
    </script>
</body>

</html>
