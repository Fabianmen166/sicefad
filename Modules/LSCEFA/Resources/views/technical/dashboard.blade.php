<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Técnico - LSCEFA</title>
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
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        .btn-feature {
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .btn-primary-feature {
            background-color: #4e73df;
            border: none;
            font-size: 1.1rem;
            padding: 15px 30px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
        }
        .btn-primary-feature:hover {
            background-color: #2e59d9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.4);
        }
        .feature-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: all 0.3s;
            height: 100%;
            border-left: 4px solid #4e73df;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(58, 59, 69, 0.2);
        }
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #4e73df;
        }
        .welcome-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 2rem 0;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Sección de bienvenida -->
        <div class="welcome-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1><i class="fas fa-tools me-2"></i>Panel de Personal Técnico</h1>
                        <p class="lead mb-0">Bienvenido, {{ $user->nickname ?? 'Técnico' }}. {{ now()->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="{{ route('lscefa.technical.analyses.index') }}" class="btn btn-light btn-lg btn-feature">
                            <i class="fas fa-flask me-2"></i> Ir a Análisis Técnicos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de características -->
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <h4>Análisis de pH</h4>
                        <p class="text-muted">Realice y gestione análisis de pH de manera eficiente.</p>
                        <a href="{{ route('lscefa.ph_analysis.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-right me-1"></i> Acceder
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-tint"></i>
                        </div>
                        <h4>Análisis de Conductividad</h4>
                        <p class="text-muted">Gestione los análisis de conductividad eléctrica.</p>
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="fas fa-clock me-1"></i> Próximamente
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Reportes</h4>
                        <p class="text-muted">Genere reportes detallados de los análisis realizados.</p>
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="fas fa-clock me-1"></i> Próximamente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card welcome-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Estadísticas Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <h3 class="text-primary">{{ $analysesCount ?? '0' }}</h3>
                                <p class="text-muted mb-0">Análisis Realizados</p>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <h3 class="text-success">{{ $pendingAnalyses ?? '0' }}</h3>
                                <p class="text-muted mb-0">Pendientes</p>
                            </div>
                            <div class="col-md-4">
                                <h3 class="text-warning">{{ $recentAnalyses ?? '0' }}</h3>
                                <p class="text-muted mb-0">Últimos 7 días</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para inicializar tooltips de Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>