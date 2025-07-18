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
        href="{{ asset('AdminLTE/https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  


    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --sena-green: #39B54A;
            --sena-dark-green: #2E8B3E;
            --sena-light-green: #D1E7DD;
        }

        /* Animación de pulso para el logo */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .logo-sena {
            animation: pulse 2s infinite;
            transition: transform 0.3s ease-in-out;
        }

        /* Estilo general */
        .main-sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            bottom: 0;
            background-color: white;
            border-right: 1px solid #dee2e6;
        }

        .content-wrapper {
            margin-left: 250px;
            background-color: #f8f9fa;
        }

        .hover-green:hover {
            color: var(--sena-green) !important;
            transition: color 0.3s ease-in-out;
        }

        .brand-link {
            border-bottom: 3px solid var(--sena-green);
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            text-decoration: none;
        }

        .brand-text {
            color: var(--sena-dark-green) !important;
            font-weight: bold !important;
            font-size: 1.2rem;
        }

        .nav-sidebar .nav-item>.nav-link {
            color: #495057;
        }

        .nav-sidebar .nav-item>.nav-link.active,
        .nav-sidebar .nav-item>.nav-link:hover {
            background-color: var(--sena-light-green);
            color: var(--sena-dark-green);
        }

        .navbar-dark {
            background-color: var(--sena-dark-green) !important;
        }

        .main-footer {
            background-color: var(--sena-dark-green) !important;
            color: white !important;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
            background-color: var(--sena-green);
            color: white;
        }

        .card {
            border-top: 3px solid var(--sena-green);
        }

        .btn-primary {
            background-color: var(--sena-green);
            border-color: var(--sena-dark-green);
        }

        .btn-primary:hover {
            background-color: var(--sena-dark-green);
            border-color: var(--sena-dark-green);
        }

        .bg-primary {
            background-color: var(--sena-green) !important;
        }
    </style>

</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>

          <!-- Right navbar links -->
<ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            {{ Auth::user()->name }}
        </a>
        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                {{('Cerrar') }}
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </li>
    @if(isset($roles))
        @if(in_array('lscefa.admin', $roles))
            <li class="nav-item">
                <a href="{{ route('lscefa.admin.welcome') }}" class="btn btn-primary mt-2">Ir al Dashboard de Administración</a>
            </li>
        @elseif(in_array('lscefa.quality', $roles))
            <li class="nav-item">
                <a href="{{ route('lscefa.quality.dashboard') }}" class="btn btn-primary mt-2">Ir al Dashboard de Calidad</a>
            </li>
        @elseif(in_array('lscefa.intern', $roles))
            <li class="nav-item">
                <a href="{{ route('lscefa.intern.panelpas') }}" class="btn btn-primary mt-2">Ir al Panel de Pasante</a>
            </li>
        @elseif(in_array('lscefa.technical', $roles))
            <li class="nav-item">
                <a href="{{ route('lscefa.technical.panel') }}" class="btn btn-primary mt-2">Ir al Panel Técnico</a>
            </li>
        @endif
    @endif
</ul>
    </div>
    </a>

    </li>
    </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-success-green elevation-4">
        <a href="#" class="brand-link">
            <img src="https://www.sena.edu.co/Style%20Library/alayout/images/logoSena.png" width="60px"
                class="logo-sena">
            <span class="brand-text font-weight-light">LABORATORIO</span>
        </a>
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">


                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="" class="nav-link text-success">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- Revisar Análisis -->
                    <li class="nav-item">
                        <a href="" class="nav-link text-success">
                            <i class="nav-icon fas fa-check-circle"></i>
                            <p>Revisar Análisis</p>
                        </a>
                    </li>

                    <!-- Funcionarios -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link text-success">
                            <i class="fas fa-users"></i>
                            <p>Funcionarios <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-edit"></i>
                                    <p>Ingreso</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-clipboard-list"></i>
                                    <p>Listas</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Bodega Finca -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link text-success">
                            <i class="fas fa-warehouse"></i>
                            <p>Bodega Finca <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item has-treeview">
                                <a href="#" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-box"></i>
                                    <p>Insumos <i class="fas fa-angle-left right"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="#" class="nav-link text-dark">
                                            <i class="nav-icon fas fa-edit"></i>
                                            <p>Ingreso</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#" class="nav-link text-dark">
                                            <i class="nav-icon fas fa-clipboard-list"></i>
                                            <p>Lista</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item has-treeview">
                                <a href="#" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-tools"></i>
                                    <p>Herramientas <i class="fas fa-angle-left right"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="#" class="nav-link text-dark">
                                            <i class="nav-icon fas fa-edit"></i>
                                            <p>Ingreso</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#" class="nav-link text-dark">
                                            <i class="nav-icon fas fa-clipboard-list"></i>
                                            <p>Listas</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <!-- Roles Mayordomo -->
                    <li class="nav-item">
                        <a href="#" class="nav-link text-success">
                            <i class="fas fa-user"></i>
                            <p>Roles Mayordomo</p>
                        </a>
                    </li>

                    <!-- Análisis de Producción -->
                    <li class="nav-item">
                        <a href="#" class="nav-link text-success">
                            <i class="fas fa-chart-bar"></i>
                            <p>Análisis de Producción</p>
                        </a>
                    </li>

                    <!-- Geo Referencias -->
                    <li class="nav-item">
                        <a href="#" class="nav-link text-success">
                            <i class="nav-icon fas fa-globe"></i>
                            <p>Geo Referencias</p>
                        </a>
                    </li>

                    <!-- Reportes -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link text-danger">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Reportes <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>Actividades</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                    <p>Contable</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        @yield('contenido')
    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('AdminLTE-3.2.0/plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI -->
    <script src="{{ asset('AdminLTE-3.2.0/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('AdminLTE-3.2.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('AdminLTE-3.2.0/dist/js/adminlte.js') }}"></script>
    <!-- Include additional scripts from child views -->
    @yield('scripts')
</body>

</html>
