<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Panel Técnico @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">

</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light" style="min-height: 40px; padding: 0.25rem 1rem;">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle text-dark fw-bold" href="#"
                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 0.9rem;">
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
        <!-- Sidebar -->
        <aside class="main-sidebar elevation-4">
            <a href="#" class="brand-link">
                <span class="brand-text font-weight-light">SLCEFA Técnico</span>
                <img src="https://www.sena.edu.co/Style%20Library/alayout/images/logoSena.png" width="40px">
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        @php $user = auth()->user(); @endphp

                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.index') }}"
                                class="nav-link {{ request()->routeIs('lscefa.technical.analyses.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-vials"></i>
                                <p>Análisis Técnicos</p>
                            </a>
                        </li>

                         
                        <!-- Gestión de pH -->
                        <li class="nav-item">
                            <a href="{{ route('lscefa.ph_analysis.index') }}" class="nav-link {{ request()->routeIs('lscefa.ph_analysis.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Análisis de pH</p>
                            </a>
                        </li>
                       
                        <!-- Análisis de Conductividad -->
                        <li class="nav-item">
                            <a href="{{ route('lscefa.conductivity_analysis.index') }}" class="nav-link {{ request()->routeIs('lscefa.conductivity_analysis.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tint"></i>
                                <p>Conductividad</p>
                            </a>
                        </li>
                       
                       
                        <!-- Gestión de Análisis de humedad -->
                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.humidity.index') }}"
                                class="nav-link {{ Route::is('lscefa.technical.analyses.humidity.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Humedad</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href=""
                                class="nav-link {{ Route::is('lscefa.technical.analyses.carbon.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Carbono Orgánico</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.micronutrients.index') }}"
                                class="nav-link {{ Route::is('lscefa.technical.analyses.micronutrients.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Micronutrientes</p>
                            </a>
                        </li>


                        <li class="nav-item">
                            <a href="" class="nav-link text-success">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Conductividad</p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.cationic.index') }}" 
                                class="nav-link {{ Route::is('lscefa.technical.analyses.cationic.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Intercambio Catiónico</p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.phosphorus.index') }}" 
                                class="nav-link {{ Route::is('lscefa.technical.analyses.phosphorus.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Fósforo</p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.sulfur.index') }}" 
                                class="nav-link {{ Route::is('lscefa.technical.analyses.sulfur.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Azufre</p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.boron.index') }}" 
                                class="nav-link {{ Route::is('lscefa.technical.analyses.boron.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Boro</p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.texture.index') }}" 
                                class="nav-link {{ Route::is('lscefa.technical.analyses.texture.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-layer-group"></i>
                                <p>Textura</p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.sulfur.index') }}" 
                                class="nav-link {{ Route::is('lscefa.technical.analyses.sulfur.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Azufre</p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.boron.index') }}" 
                                class="nav-link {{ Route::is('lscefa.technical.analyses.boron.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Boro</p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.texture.index') }}" 
                                class="nav-link {{ Route::is('lscefa.technical.analyses.texture.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-layer-group"></i>
                                <p>Textura</p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="" 
                                class="nav-link {{ Route::is('lscefa.technical.analyses.exchangeable_bases.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Bases Cambiables</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
        </aside>
        <!-- Content Wrapper -->
        <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header" style="padding: 0.5rem 0;">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-12">
                    <h1 class="m-0" style="font-size: 1.5rem;">@yield('title')</h1>
                </div>
            </div>

    <!-- Main content -->
    <section class="content pt-2">
        <div class="container-fluid">
            @yield('content')
        </div>

        <footer class="main-footer">
            <strong>Copyright © {{ date('Y') }} <a href="#">SENA</a>.</strong>
            Todos los derechos reservados.
        </footer>
    </div>
    <!-- REQUIRED SCRIPTS -->
    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.js') }}"></script>
    @stack('scripts')
</body>

</html>

<style>
    :root {
        --sena-green: #39B54A;
        --sena-dark-green: #2E8B3E;
        --sena-light-green: #D1E7DD;
        --sidebar-width: 250px;
        --header-height: 60px;
        --transition-speed: 0.3s;
    }

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
        border-right: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
        width: var(--sidebar-width);
        transition: all var(--transition-speed) ease-in-out;
    }

    .content-wrapper {
        margin-left: var(--sidebar-width);
        background-color: #f8f9fa;
        min-height: 100vh;
        transition: margin var(--transition-speed) ease-in-out;
    }

        .main-header {
            background: white !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            height: 40px !important;
            min-height: 40px !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

    .nav-sidebar .nav-item>.nav-link {
        color: var(--sena-green);
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 0.95rem;
        transition: all var(--transition-speed) ease;
    }

    .nav-sidebar .nav-item>.nav-link:hover {
        background-color: var(--sena-light-green);
        color: var(--sena-dark-green);
        transform: translateX(5px);
    }

    .nav-sidebar .nav-item>.nav-link.active {
        background-color: var(--sena-green);
        color: white;
        box-shadow: 0 2px 4px rgba(57, 181, 74, 0.2);
    }

    .nav-sidebar .nav-item>.nav-link i {
        margin-right: 10px;
        width: 18px;
        text-align: center;
    }

    .main-footer {
        background-color: white !important;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        color: #6c757d !important;
        padding: 1rem;
        text-align: center;
    }
</style>
