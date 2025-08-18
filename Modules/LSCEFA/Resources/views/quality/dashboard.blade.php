<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Calidad - LSCEFA</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f6f7fb;
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
        }
        .welcome-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 2rem;
        }
        .card-header { background-color: #14532d; color: white; border-radius: 10px 10px 0 0 !important; }
        .btn-cotizaciones {
            background-color: #0f766e;
            color: white;
            font-weight: 600;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .btn-cotizaciones:hover {
            background-color: #115e59;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: white;
        }
        .welcome-section { background: #ffffff; border: 1px solid #edf2f7; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 0.15rem 1.25rem rgba(58, 59, 69, 0.08); }
        h1,h2,h3,h4,h5 { letter-spacing: .2px; }
        .fw-semibold { font-weight: 600; }
        .kpi-card { background: #fff; border: 1px solid #edf2f7; border-radius: 12px; padding: 1rem 1.25rem; box-shadow: 0 0.1rem 0.8rem rgba(58,59,69,.06); }
        .kpi-title { font-size: .9rem; color: #6b7280; margin-bottom: .25rem; }
        .kpi-value { font-size: 1.35rem; font-weight: 700; color: #111827; }
        .kpi-trend { font-size: .85rem; }
        .hero-surface {
            background: linear-gradient(135deg,#0f766e 0%, #14532d 100%);
            position: relative; overflow: hidden; border-radius: 16px;
        }
        .hero-surface:after {
            content:""; position:absolute; right:-10%; top:-30%; width:50%; height:150%;
            background: radial-gradient(closest-side, rgba(255,255,255,.12), rgba(255,255,255,0));
            transform: rotate(18deg);
        }
        /* Sutil patrón SVG para temática laboratorio/suelo */
        .hero-pattern { position:absolute; inset:0; opacity:.15; pointer-events:none; }
        .note { font-size: .9rem; color: #6b7280; }
        .breadcrumb a { text-decoration:none; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-2">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 text-gray-800 mb-0">Dashboard de Calidad</h1>
                    <span class="text-muted">{{ now()->format('d/m/Y') }}</span>
                </div>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.quality.dashboard') }}"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Calidad</li>
                    </ol>
                </nav>
                <hr>
            </div>
        </div>

        <!-- Hero profesional con temática laboratorio/suelo -->
        <section class="row mb-4">
            <div class="col-12">
                <div class="p-4 p-lg-5 mb-4 text-white hero-surface" style="box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.08);">
                    <!-- Patrón SVG -->
                    <svg class="hero-pattern" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" viewBox="0 0 800 200">
                        <defs>
                            <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
                                <stop offset="0%" stop-color="#ffffff"/>
                                <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <g fill="url(#g)">
                            <circle cx="120" cy="60" r="24"/>
                            <circle cx="320" cy="120" r="18"/>
                            <rect x="520" y="40" width="36" height="36" rx="6"/>
                            <rect x="680" y="120" width="28" height="28" rx="6"/>
                        </g>
                    </svg>
                    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between position-relative">
                        <div class="mb-3 mb-lg-0">
                            <h2 class="fw-bold mb-2">Gestión de Calidad | Laboratorio de Análisis de Suelos</h2>
                            <p class="mb-0 opacity-75">Bienvenido, {{ auth()->user()->name ?? 'Usuario' }}. Centraliza el control de procesos, resultados analíticos y aseguramiento de la calidad conforme a buenas prácticas.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('lscefa.quality.quotes.index') }}" class="btn btn-light text-teal fw-semibold">
                                <i class="fas fa-file-invoice-dollar me-2"></i> Cotizaciones
                            </a>
                            @if(auth()->user() && (auth()->user()->havePermission('lscefa.quality.processes.index') || auth()->user()->havePermission('lscefa.admin.processes.index')))
                            <a href="{{ route('lscefa.quality.processes.index') }}" class="btn btn-outline-light fw-semibold">
                                <i class="fas fa-tasks me-2"></i> Procesos
                            </a>
                            @endif
                            @if(auth()->user() && (auth()->user()->havePermission('lscefa.quality.processes.index') || auth()->user()->havePermission('lscefa.admin.processes.index')))
                            <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-outline-light fw-semibold">
                                <i class="fas fa-clipboard-check me-2"></i> Revisiones
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- KPIs principales (placeholders) -->
        <section class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title"><i class="fas fa-flask me-1"></i> Procesos activos</div>
                    <div class="kpi-value">—</div>
                    <div class="kpi-trend text-success"><i class="fas fa-arrow-up"></i> Últimos 7 días</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title"><i class="fas fa-truck me-1"></i> Entregas esta semana</div>
                    <div class="kpi-value">—</div>
                    <div class="kpi-trend text-muted">Programadas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title"><i class="fas fa-exclamation-triangle me-1"></i> No conformidades</div>
                    <div class="kpi-value">—</div>
                    <div class="kpi-trend text-danger"><i class="fas fa-exclamation-triangle"></i> Atención</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-title"><i class="fas fa-hourglass-half me-1"></i> Tiempo medio (días)</div>
                    <div class="kpi-value">—</div>
                    <div class="kpi-trend text-muted">Ciclo de proceso</div>
                </div>
            </div>
        </section>

        <section class="row gy-4">
            <div class="col-12 col-lg-7">
                <div class="welcome-section">
                    <h4 class="mb-3"><i class="fas fa-bullseye text-primary me-2"></i> Capacidades del módulo de Calidad</h4>
                    <ul class="mb-3 ps-3">
                        <li class="mb-2">Supervisar la <strong>gestión de procesos</strong> desde la recepción de muestras hasta la entrega de resultados.</li>
                        <li class="mb-2">Administrar <strong>tipos de clientes</strong>, acuerdos y condiciones comerciales aplicadas.</li>
                        <li class="mb-2">Monitorear <strong>fechas de entrega</strong> y priorizar actividades críticas del laboratorio.</li>
                        <li class="mb-2">Mantener <strong>trazabilidad documental</strong>, anexos y comunicación con clientes.</li>
                    </ul>
                    <p class="text-muted mb-0">Este panel ofrece una visión clara y ejecutiva de la operación de calidad, alineada a prácticas profesionales para laboratorios de análisis de suelos.</p>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="welcome-section">
                    <h4 class="mb-3"><i class="fas fa-bolt text-warning me-2"></i> Acciones rápidas</h4>
                    <div class="d-grid gap-2">
                        <a href="{{ route('lscefa.quality.customer_types.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-users-cog me-2"></i> Gestionar tipos de cliente
                        </a>
                        @if(auth()->user() && (auth()->user()->havePermission('lscefa.quality.processes.index') || auth()->user()->havePermission('lscefa.admin.processes.index')))
                        <a href="{{ route('lscefa.quality.processes.index') }}" class="btn btn-outline-warning">
                            <i class="fas fa-stream me-2"></i> Ver procesos iniciados
                        </a>
                        @endif
                        <a href="{{ route('lscefa.quality.quotes.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-file-alt me-2"></i> Listado de cotizaciones
                        </a>
                        @if(auth()->user() && (auth()->user()->havePermission('lscefa.quality.processes.index') || auth()->user()->havePermission('lscefa.admin.processes.index')))
                        <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-clipboard-check me-2"></i> Revisiones pendientes
                        </a>
                        @endif
                        <div class="note mt-2"><i class="fas fa-shield-alt me-1"></i> Operación orientada a cumplimiento. Para auditoría y controles avanzados, solicite acceso a Calidad.</div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="mt-4 text-center text-muted small">
            <span>Laboratorio de Análisis de Suelos • LSCEFA • Calidad</span>
        </footer>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>