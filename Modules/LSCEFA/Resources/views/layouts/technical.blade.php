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
                            <a href="{{ route('lscefa.technical.analyses.carbon.index') }}"
                                class="nav-link {{ Route::is('lscefa.technical.analyses.carbon.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Carbono Orgánico</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('lscefa.technical.analyses.acidity.index') }}"
                                class="nav-link {{ Route::is('lscefa.technical.analyses.acidity.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Acidez</p>
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
                            <a href="{{ route('lscefa.technical.analyses.exchangeable_bases.index') }}" 
                                class="nav-link {{ Route::is('lscefa.technical.analyses.exchangeable_bases.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <p>Bases Cambiables</p>
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

                    </ul>
                </nav>
            </div>
        </aside>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-1">
                        <div class="col-sm-12">
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
        --sidebar-width: 240px;
        --header-height: 40px;
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
        padding: 0; /* Eliminamos padding extra */
    }

    .main-header {
        background: white !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        height: var(--header-height) !important;
        min-height: var(--header-height) !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-left: var(--sidebar-width); /* Alineamos el header con el contenido */
    }

    /* Content Header más compacto */
    .content-header {
        padding: 0.5rem 1rem !important; /* Reducimos padding */
        background-color: white;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 0;
    }

    .content-header h1 {
        font-size: 1.25rem !important; /* Título más pequeño */
        margin: 0 !important;
        color: var(--sena-dark-green);
    }

    /* Main content más pegado */
    .content {
        padding: 0.75rem 1rem !important; /* Reducimos padding significativamente */
    }

    .navbar-nav .nav-link {
        color: var(--sena-dark-green) !important;
        font-weight: 500;
        padding: 0.3rem 0.8rem; /* Reducimos padding del navbar */
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
        padding: 12px; /* Reducimos padding */
        text-decoration: none;
        background: white;
        transition: all var(--transition-speed) ease;
        flex-direction: row-reverse;
    }

    .brand-text {
        color: var(--sena-dark-green) !important;
        font-weight: 700 !important;
        font-size: 1.1rem; /* Ligeramente más pequeño */
        letter-spacing: 0.5px;
    }

    .nav-sidebar .nav-item {
        margin: 2px 6px; /* Reducimos margen entre items */
    }

    .nav-sidebar .nav-item>.nav-link {
        color: var(--sena-green);
        border-radius: 6px; /* Radio más pequeño */
        padding: 8px 12px; /* Reducimos padding */
        font-size: 0.9rem; /* Texto ligeramente más pequeño */
        transition: all var(--transition-speed) ease;
    }

    .nav-sidebar .nav-item>.nav-link:hover {
        background-color: var(--sena-light-green);
        color: var(--sena-dark-green);
        transform: translateX(3px); /* Menos desplazamiento */
    }

    .nav-sidebar .nav-item>.nav-link.active {
        background-color: var(--sena-green);
        color: white;
        box-shadow: 0 2px 4px rgba(57, 181, 74, 0.2);
    }

    .nav-sidebar .nav-item>.nav-link i {
        margin-right: 8px; /* Menos espacio entre icono y texto */
        width: 16px;
        text-align: center;
        font-size: 0.9rem;
    }

    .main-footer {
        background-color: white !important;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        color: #6c757d !important;
        padding: 0.75rem; /* Reducimos padding del footer */
        text-align: center;
        margin-left: var(--sidebar-width);
        font-size: 0.85rem;
    }

    /* Ajustes para las cards del formulario */
    .card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin-bottom: 1rem;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1rem; /* Padding más compacto */
    }

    .card-body {
        padding: 1rem; /* Padding más compacto */
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        :root {
            --sidebar-width: 200px;
        }
        
        .brand-text {
            font-size: 1rem !important;
        }
        
        .nav-sidebar .nav-item>.nav-link {
            font-size: 0.85rem;
            padding: 6px 10px;
        }
    }
</style>