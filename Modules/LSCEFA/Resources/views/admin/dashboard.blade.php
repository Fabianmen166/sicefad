<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - LSCEFA</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fc;
        }
        .welcome-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 2rem;
            transition: transform 0.3s;
        }
        .welcome-card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-admin {
            font-weight: 600;
            padding: 12px 25px;
            font-size: 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .btn-cotizaciones {
            background-color: #1cc88a;
            color: white;
        }
        .btn-cotizaciones:hover {
            background-color: #17a673;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: white;
        }
        .welcome-section {
            background: white;
            border-radius: 10px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        .admin-stats {
            font-size: 1.1rem;
            color: #5a5c69;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 text-gray-800">Panel de Administración LSCEFA</h1>
                    <span class="text-muted">{{ now()->format('d/m/Y') }}</span>
                </div>
                <hr>
            </div>
        </div>

        <!-- Sección de Bienvenida -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-section text-center">
                    <h2 class="mb-3">Bienvenido, {{ auth()->user()->name ?? 'Administrador' }}</h2>
                    <p class="lead mb-4">Sistema de Gestión Integral LSCEFA</p>
                    <div class="admin-stats mb-4">
                        <div class="row justify-content-center">
                            <div class="col-md-3 mb-3">
                                <i class="fas fa-users me-2"></i> Usuarios: 
                                <span class="fw-bold">{{ $usersCount ?? '0' }}</span>
                            </div>
                            <div class="col-md-3 mb-3">
                                <i class="fas fa-file-invoice-dollar me-2"></i> Cotizaciones: 
                                <span class="fw-bold">{{ $quotesCount ?? '0' }}</span>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-tasks me-2"></i> Procesos Activos: 
                                <span class="fw-bold">{{ $activeProcesses ?? '0' }}</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('lscefa.quality.quotes.index') }}" class="btn btn-cotizaciones btn-admin">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Gestionar Cotizaciones
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Acceso Rápido -->
        <div class="row">
            <!-- Gestión de Usuarios -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 welcome-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users-cog me-2"></i> Gestión de Usuarios
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Administre los usuarios, roles y permisos del sistema.</p>
                        <a href="#" class="btn btn-primary btn-admin">
                            <i class="fas fa-arrow-right me-1"></i> Acceder
                        </a>
                    </div>
                </div>
            </div>

            <!-- Configuración del Sistema -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 welcome-card">
                    <div class="card-header bg-success">
                        <h5 class="card-title text-white mb-0">
                            <i class="fas fa-cog me-2"></i> Configuración
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Configure los parámetros generales del sistema.</p>
                        <a href="#" class="btn btn-success btn-admin">
                            <i class="fas fa-arrow-right me-1"></i> Configurar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reportes -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 welcome-card">
                    <div class="card-header bg-warning">
                        <h5 class="card-title text-white mb-0">
                            <i class="fas fa-chart-bar me-2"></i> Reportes
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Genere y visualice reportes del sistema.</p>
                        <a href="#" class="btn btn-warning text-white btn-admin">
                            <i class="fas fa-arrow-right me-1"></i> Ver Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Efecto de hover mejorado para las tarjetas
        document.querySelectorAll('.welcome-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15)';
            });
        });
    </script>
</body>
</html>