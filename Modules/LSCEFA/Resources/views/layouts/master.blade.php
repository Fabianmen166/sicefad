<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('images/Favicon2.png') }}" type="image/x-icon">
    <title>Bienvenido Admin @yield('title')</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="{{ asset('adminlte/https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --sena-green: #39B54A;
            --sena-dark-green: #2E8B3E;
            --sena-light-green: #D1E7DD;
            --sena-hover-green: #4CC55D;
            --sidebar-width: 250px;
            --header-height: 60px;
            --transition-speed: 0.3s;
        }

        /* Animación de pulso para el logo */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .logo-sena {
            animation: pulse 2s infinite;
            transition: transform var(--transition-speed) ease-in-out;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        /* Estilo general */
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background-color: #f8f9fa;
        }

        .main-sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            bottom: 0;
            background-color: white;
            border-right: 1px solid rgba(0,0,0,0.1);
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            width: var(--sidebar-width);
            transition: all var(--transition-speed) ease-in-out;
        }

        .content-wrapper {
            margin-left: var(--sidebar-width);
            background-color: #f8f9fa;
            min-height: 100vh;
            transition: margin var(--transition-speed) ease-in-out;
        }

        /* Navbar styling */
        .main-header {
            background: white !important;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            height: var(--header-height);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .navbar-nav .nav-link {
            color: var(--sena-dark-green) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all var(--transition-speed) ease;
        }

        .navbar-nav .nav-link:hover {
            color: var(--sena-green) !important;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .dropdown-item {
            padding: 0.7rem 1.5rem;
            transition: all var(--transition-speed) ease;
        }

        .dropdown-item:hover {
            background-color: var(--sena-light-green);
            color: var(--sena-dark-green);
        }

        /* Sidebar styling */
        .brand-link {
            border-bottom: 2px solid var(--sena-green);
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            text-decoration: none;
            background: white;
            transition: all var(--transition-speed) ease;
            flex-direction: row-reverse;
        }

        .brand-text {
            color: var(--sena-dark-green) !important;
            font-weight: 700 !important;
            font-size: 1.2rem;
            letter-spacing: 0.5px;
        }

        .nav-sidebar .nav-item {
            margin: 4px 8px;
        }

        .nav-sidebar .nav-item > .nav-link {
            color: var(--sena-green);
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 0.95rem;
            transition: all var(--transition-speed) ease;
        }

        .nav-sidebar .nav-item > .nav-link:hover {
            background-color: var(--sena-light-green);
            color: var(--sena-dark-green);
            transform: translateX(5px);
        }

        .nav-sidebar .nav-item > .nav-link.active {
            background-color: var(--sena-green);
            color: white;
            box-shadow: 0 2px 4px rgba(57, 181, 74, 0.2);
        }

        .nav-sidebar .nav-item > .nav-link i {
            margin-right: 10px;
            width: 18px;
            text-align: center;
        }

        /* Card styling */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding: 1.25rem;
        }

        /* Button styling */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all var(--transition-speed) ease;
        }

        .btn-primary {
            background-color: var(--sena-green);
            border-color: var(--sena-green);
        }

        .btn-primary:hover {
            background-color: var(--sena-hover-green);
            border-color: var(--sena-hover-green);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(57, 181, 74, 0.2);
        }

        /* Footer styling */
        .main-footer {
            background-color: white !important;
            border-top: 1px solid rgba(0,0,0,0.1);
            color: #6c757d !important;
            padding: 1rem;
            text-align: center;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--sena-green);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--sena-dark-green);
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle text-dark fw-bold" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ Auth::user() ? Auth::user()->name : 'Usuario' }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Cerrar Sesión') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-success-green elevation-4">
            <a href="#" class="brand-link">
                <span class="brand-text font-weight-light">SLCEFA</span>
                <img src="https://www.sena.edu.co/Style%20Library/alayout/images/logoSena.png" width="50px" class="logo-sena">
            </a>
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false" style="gap: 8px;">
                        <!-- Cotizaciones -->
                        <li class="nav-item">
                            <a href="{{ route('lscefa.quality.quotes.index') }}" class="nav-link">
                                <i class="fas fa-file-invoice-dollar nav-icon"></i>
                                <p>Cotizaciones</p>
                            </a>
                        </li>
                        <!-- Clientes -->
                        <li class="nav-item">
                            <a href="{{ route('lscefa.quality.customers.index') }}" class="nav-link">
                                <i class="fas fa-users nav-icon"></i>
                                <p>Clientes</p>
                            </a>
                        </li>
                        <!-- Servicios -->
                        <li class="nav-item">
                            <a href="{{ route('lscefa.quality.services.index') }}" class="nav-link">
                                <i class="fas fa-cogs nav-icon"></i>
                                <p>Servicios</p>
                            </a>
                        </li>
                        <!-- Paquetes de Servicio -->
                        <li class="nav-item">
                            <a href="{{ route('lscefa.quality.service_packages.index') }}" class="nav-link">
                                <i class="fas fa-box nav-icon"></i>
                                <p>Paquetes de Servicio</p>
                            </a>
                        </li>
                        <!-- Tipos de Cliente -->
                        <li class="nav-item">
                            <a href="{{ route('lscefa.quality.customer_types.index') }}" class="nav-link">
                                <i class="fas fa-user-tag nav-icon"></i>
                                <p>Tipos de Cliente</p>
                            </a>
                        </li>
                        @php
                            $user = auth()->user();
                        @endphp
                        @if($user && ($user->havePermission('lscefa.quality.processes.index') || $user->havePermission('lscefa.admin.processes.index')))
                            <li class="nav-item">
                                <a href="{{ route('lscefa.quality.processes.index') }}" class="nav-link">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>Procesos Iniciados</p>
                                </a>
                            </li>
                        @endif
                        
                        <!-- Menú de Administración - Solo visible para administradores -->
                       
                            
                            <li class="nav-item">
                                <a href="{{ route('lscefa.admin.users.index') }}" class="nav-link {{ request()->routeIs('lscefa.admin.users.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <p>Gestión de Usuarios</p>
                                </a>
                            </li>
                      
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('title')</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        <!-- Main Footer -->
        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">SENA</a>.</strong>
            Todos los derechos reservados.
        </footer>
    </div>

    <!-- REQUIRED SCRIPTS -->
    <!-- jQuery -->
    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap -->
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE -->
    <script src="{{ asset('adminlte/dist/js/adminlte.js') }}"></script>
    @stack('scripts')
</body>
</html>