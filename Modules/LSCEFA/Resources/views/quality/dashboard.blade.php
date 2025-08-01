<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Calidad - LSCEFA</title>
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
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-cotizaciones {
            background-color: #1cc88a;
            color: white;
            font-weight: 600;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: all 0.3s;
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
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 text-gray-800">Dashboard de Calidad</h1>
                    <span class="text-muted">{{ now()->format('d/m/Y') }}</span>
                </div>
                <hr>
            </div>
        </div>

        <!-- Sección de Bienvenida -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-section text-center">
                    <h2 class="mb-4">Bienvenido, {{ auth()->user()->name ?? 'Usuario' }}</h2>
                    <p class="lead mb-4">Sistema de Gestión de Calidad LSCEFA</p>
                    <a href="{{ route('lscefa.quality.quotes.index') }}" class="btn btn-cotizaciones">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Ir a Cotizaciones
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Acceso Rápido -->
        <div class="row">
            <!-- Tipos de Cliente -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 welcome-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i> Tipos de Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Gestión de tipos de cliente y sus características.</p>
                        <a href="{{ route('lscefa.quality.customer_types.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-right me-1"></i> Acceder
                        </a>
                    </div>
                </div>
            </div>

            <!-- Procesos Iniciados -->
            @if(auth()->user() && (auth()->user()->havePermission('lscefa.quality.processes.index') || auth()->user()->havePermission('lscefa.admin.processes.index')))
            <div class="col-md-4 mb-4">
                <div class="card h-100 welcome-card">
                    <div class="card-header bg-warning">
                        <h5 class="card-title text-white mb-0">
                            <i class="fas fa-tasks me-2"></i> Procesos Iniciados
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Ver y gestionar todos los procesos iniciados en el sistema.</p>
                        <a href="{{ route('lscefa.quality.processes.index') }}" class="btn btn-warning text-white">
                            <i class="fas fa-arrow-right me-1"></i> Ver Procesos
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Estándares de Calidad -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 welcome-card">
                    <div class="card-header bg-success">
                        <h5 class="card-title text-white mb-0">
                            <i class="fas fa-certificate me-2"></i> Estándares de Calidad
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Configuración y gestión de estándares de calidad.</p>
                        <a href="#" class="btn btn-success">
                            <i class="fas fa-arrow-right me-1"></i> Gestionar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Auditorías -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 welcome-card">
                    <div class="card-header bg-info">
                        <h5 class="card-title text-white mb-0">
                            <i class="fas fa-clipboard-check me-2"></i> Auditorías
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Gestión de auditorías y seguimiento de calidad.</p>
                        <a href="#" class="btn btn-info">
                            <i class="fas fa-arrow-right me-1"></i> Gestionar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>