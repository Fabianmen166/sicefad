<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\LSCEFA\Http\Controllers\LSCEFAController;
use Modules\LSCEFA\Http\Controllers\CustomerTypeController;
use Modules\LSCEFA\Http\Controllers\ServiceController;
use Modules\LSCEFA\Http\Controllers\ServicePackageController;
use Modules\LSCEFA\Http\Controllers\CustomerController;
use Modules\LSCEFA\Http\Controllers\QuoteController;
use Modules\LSCEFA\Http\Middleware\CheckLSCEFARole;
use Modules\LSCEFA\Http\Controllers\TechnicalAnalysisController;
use Modules\LSCEFA\Http\Controllers\HumidityAnalysisController;
use Modules\LSCEFA\Http\Controllers\ConductivityAnalysisController;
use Modules\LSCEFA\Http\Controllers\AcidezAnalysisController;
use Modules\LSCEFA\Http\Controllers\UserManagementController;
use Modules\LSCEFA\Http\Controllers\CationicAnalysisController;
use Modules\LSCEFA\Http\Controllers\PhosphorusAnalysisController;
use Modules\LSCEFA\Http\Controllers\SulfurAnalysisController;
use Modules\LSCEFA\Http\Controllers\ExchangeableBasesAnalysisController;
// use Modules\LSCEFA\Http\Controllers\BoronAnalysisController; // Archivo no existe
use Modules\LSCEFA\Http\Controllers\CarbonoAnalysisController;
use Modules\LSCEFA\Http\Controllers\MicronutrientsAnalysisController;
use Modules\LSCEFA\Http\Controllers\TextureAnalysisController;
use Modules\LSCEFA\Http\Controllers\FileDownloadController;
use Modules\LSCEFA\Http\Controllers\PhAnalysisController;
use Modules\LSCEFA\Http\Controllers\AnalyticalController;
use Modules\LSCEFA\Http\Controllers\QuoteSearchController;
use Modules\LSCEFA\Http\Controllers\QuoteFileController;
use Modules\LSCEFA\Http\Controllers\MicronutrientsAnalysisController2;
use Modules\LSCEFA\Http\Controllers\BoronAnalysisController2;

Route::middleware(['lang'])->group(function(){
    Route::prefix('lscefa')->group(function () {
        // Rutas públicas
        Route::get('/index', [LSCEFAController::class, 'index'])->name('cefa.lscefa.index');

        // Rutas protegidas por rol
        Route::middleware(['auth', 'lscefa.role:lscefa.admin'])->group(function () {
            Route::get('/admin/welcome', [LSCEFAController::class, 'admin'])
                ->name('lscefa.admin.welcome');
                
            Route::get('/admin/config', [LSCEFAController::class, 'config'])
                ->name('lscefa.admin.config');
                
            // Rutas para la gestión de usuarios
            Route::prefix('admin')->group(function () {
                Route::resource('users', 'UserManagementController', [
                    'names' => [
                        'index' => 'lscefa.admin.users.index',
                        'create' => 'lscefa.admin.users.create',
                        'store' => 'lscefa.admin.users.store',
                        'edit' => 'lscefa.admin.users.edit',
                        'update' => 'lscefa.admin.users.update',
                        'destroy' => 'lscefa.admin.users.destroy',
                    ]
                ])->except(['show']); // Excluimos la ruta show ya que no la estamos usando
            });

            // Historial de procesos (solo Admin)
            Route::get('admin/process-history', [\Modules\LSCEFA\Http\Controllers\ProcessHistoryController::class, 'index'])
                ->name('lscefa.admin.process_history.index')
                ->middleware('lscefa.permission:lscefa.admin.process_history.index');
            Route::get('admin/process-history/{processId}', [\Modules\LSCEFA\Http\Controllers\ProcessHistoryController::class, 'show'])
                ->name('lscefa.admin.process_history.show')
                ->middleware('lscefa.permission:lscefa.admin.process_history.show');
        }); // Cierre del grupo de rutas de admin

        Route::middleware(['auth', 'lscefa.role:lscefa.intern'])->group(function () {
            Route::get('/intern/panelpas', [LSCEFAController::class, 'intern'])->name('lscefa.intern.panelpas');
            Route::get('/intern/tasks', [LSCEFAController::class, 'tasks'])->name('lscefa.intern.tasks');
        });

        Route::middleware(['auth', 'lscefa.role:lscefa.technical'])->group(function () {
            Route::get('/technical/panel', [LSCEFAController::class, 'technical'])->name('lscefa.technical.panel');
            Route::get('/technical/samples', [LSCEFAController::class, 'samples'])->name('lscefa.technical.samples');
            Route::get('/technical/analyses', [\Modules\LSCEFA\Http\Controllers\TechnicalAnalysisController::class, 'index'])->name('lscefa.technical.analyses.index');
        });
            
        // Rutas para análisis de pH - Accesible tanto para técnicos como para calidad
        Route::middleware(['auth', 'lscefa.role:lscefa.technical,lscefa.quality'])->group(function () {
            // Listado de análisis de pH
            Route::get('/ph-analyses', [\Modules\LSCEFA\Http\Controllers\PhAnalysisController::class, 'index'])
                ->name('lscefa.ph_analysis.index');
                
            // Procesar múltiples análisis
            Route::get('/ph-analyses/process-all', [\Modules\LSCEFA\Http\Controllers\PhAnalysisController::class, 'processAll'])
                ->name('lscefa.ph_analysis.process_all');
                
            // Mostrar formulario para un análisis específico
            Route::get('/ph-analyses/{processId}/{serviceId}', [\Modules\LSCEFA\Http\Controllers\PhAnalysisController::class, 'phAnalysis'])
                ->name('lscefa.ph_analysis.show');
                
            // Guardar un análisis
            Route::post('/ph-analyses', [\Modules\LSCEFA\Http\Controllers\PhAnalysisController::class, 'storePhAnalysis'])
                ->name('lscefa.ph_analysis.store');
                
            // Descargar reporte
            Route::get('/ph-analyses/{analysisId}/download', [\Modules\LSCEFA\Http\Controllers\PhAnalysisController::class, 'downloadPhReport'])
                ->name('lscefa.ph_analysis.download');
                
            // Procesar análisis por lote
            Route::post('/ph-analyses/batch', [\Modules\LSCEFA\Http\Controllers\PhAnalysisController::class, 'batchPhAnalysis'])->name('lscefa.ph_analysis.batch_ph_analysis');

            // Rutas para análisis de Conductividad
            Route::get('/conductivity-analyses', [\Modules\LSCEFA\Http\Controllers\ConductivityAnalysisController::class, 'index'])->name('lscefa.conductivity_analysis.index');
            Route::post('/conductivity-analyses/batch', [\Modules\LSCEFA\Http\Controllers\ConductivityAnalysisController::class, 'batchConductivityAnalysis'])->name('lscefa.conductivity_analysis.batch_conductivity_analysis');
            Route::get('/conductivity-analyses/process-all', [\Modules\LSCEFA\Http\Controllers\ConductivityAnalysisController::class, 'processAll'])->name('lscefa.conductivity_analysis.process_all');
            Route::get('/conductivity-analyses/{processId}/{serviceId}', [\Modules\LSCEFA\Http\Controllers\ConductivityAnalysisController::class, 'show'])->name('lscefa.conductivity_analysis.show');
            Route::post('/conductivity-analyses/store', [\Modules\LSCEFA\Http\Controllers\ConductivityAnalysisController::class, 'storeConductivityAnalysis'])->name('lscefa.conductivity_analysis.store');

            // Rutas para Análisis de Textura (MOVIDAS AQUÍ)
            Route::get('/technical/analyses/texture', [TextureAnalysisController::class, 'index'])->name('lscefa.technical.analyses.texture.index');
            Route::post('/technical/analyses/texture/store', [TextureAnalysisController::class, 'storeTextureAnalysis'])->name('lscefa.technical.analyses.texture.store');
            Route::get('/technical/analyses/texture/batch', [TextureAnalysisController::class, 'batchProcess'])->name('lscefa.technical.analyses.texture.batch');
            Route::post('/technical/analyses/texture/batch', [TextureAnalysisController::class, 'batchProcess'])
                ->name('lscefa.technical.analyses.texture.batch.post')
                ->middleware('lscefa.permission:lscefa.technical.analyses.texture.batch.post');
            Route::post('/technical/analyses/texture/batch-store', [TextureAnalysisController::class, 'batchStore'])->name('lscefa.technical.analyses.texture.batch_store')
                ->middleware('lscefa.permission:lscefa.technical.analyses.texture.batch_store');
            Route::get('/technical/analyses/texture/{id}/edit-rejected', [TextureAnalysisController::class, 'editRejected'])->name('lscefa.technical.analyses.texture.edit_rejected')
                ->middleware('lscefa.permission:lscefa.technical.analyses.texture.edit_rejected');
            Route::post('/technical/analyses/texture/{id}/update-rejected', [TextureAnalysisController::class, 'updateRejected'])->name('lscefa.technical.analyses.texture.update_rejected')
                ->middleware('lscefa.permission:lscefa.technical.analyses.texture.update_rejected');
            Route::get('/technical/analyses/texture/{id}', [TextureAnalysisController::class, 'show'])->name('lscefa.technical.analyses.texture.show');
            Route::get('/technical/analyses/texture/{id}/edit', [TextureAnalysisController::class, 'edit'])->name('lscefa.technical.analyses.texture.edit');
            Route::put('/technical/analyses/texture/{id}', [TextureAnalysisController::class, 'update'])->name('lscefa.technical.analyses.texture.update');
            Route::delete('/technical/analyses/texture/{id}', [TextureAnalysisController::class, 'destroy'])->name('lscefa.technical.analyses.texture.destroy');
            Route::get('/technical/analyses/texture/{id}/report', [TextureAnalysisController::class, 'report'])->name('lscefa.technical.analyses.texture.report');
            Route::get('/technical/analyses/texture/{analysisId}/download', [TextureAnalysisController::class, 'downloadTextureReport'])->name('lscefa.technical.analyses.texture.download');
        });

        // Rutas protegidas por rol para admin y gestión de calidad
        Route::middleware(['auth', 'lscefa.role:lscefa.admin,lscefa.quality'])->group(function () {
            Route::get('/quality/dashboard', [LSCEFAController::class, 'qualityDashboard'])->name('lscefa.quality.dashboard');
            
            // Rutas para tipos de cliente
            Route::get('/customer_types', [CustomerTypeController::class, 'index'])->name('lscefa.quality.customer_types.index');
            Route::get('/customer_types/create', [CustomerTypeController::class, 'create'])->name('lscefa.quality.customer_types.create');
            Route::post('/customer_types', [CustomerTypeController::class, 'store'])->name('lscefa.quality.customer_types.store');
            Route::get('/customer_types/{customerType}/edit', [CustomerTypeController::class, 'edit'])->name('lscefa.quality.customer_types.edit');
            Route::put('/customer_types/{customerType}', [CustomerTypeController::class, 'update'])->name('lscefa.quality.customer_types.update');
            Route::delete('/customer_types/{customerType}', [CustomerTypeController::class, 'destroy'])->name('lscefa.quality.customer_types.destroy');

            // Rutas para servicios
            Route::get('/services', [ServiceController::class, 'index'])->name('lscefa.quality.services.index');
            Route::get('/services/create', [ServiceController::class, 'create'])->name('lscefa.quality.services.create');
            Route::post('/services', [ServiceController::class, 'store'])->name('lscefa.quality.services.store');
            Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('lscefa.quality.services.edit');
            Route::put('/services/{service}', [ServiceController::class, 'update'])->name('lscefa.quality.services.update');
            Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('lscefa.quality.services.destroy');

            // Rutas para paquetes de servicio
            Route::get('/service_packages', [ServicePackageController::class, 'index'])->name('lscefa.quality.service_packages.index');
            Route::get('/service_packages/create', [ServicePackageController::class, 'create'])->name('lscefa.quality.service_packages.create');
            Route::post('/service_packages', [ServicePackageController::class, 'store'])->name('lscefa.quality.service_packages.store');
            Route::get('/service_packages/{servicePackage}/edit', [ServicePackageController::class, 'edit'])->name('lscefa.quality.service_packages.edit');
            Route::put('/service_packages/{servicePackage}', [ServicePackageController::class, 'update'])->name('lscefa.quality.service_packages.update');
            Route::delete('/service_packages/{servicePackage}', [ServicePackageController::class, 'destroy'])->name('lscefa.quality.service_packages.destroy');

            // Rutas para clientes
            Route::get('/customers', [CustomerController::class, 'index'])->name('lscefa.quality.customers.index');
            Route::get('/customers/create', [CustomerController::class, 'create'])->name('lscefa.quality.customers.create');
            Route::post('/customers', [CustomerController::class, 'store'])->name('lscefa.quality.customers.store');
            Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('lscefa.quality.customers.edit');
            Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('lscefa.quality.customers.update');
            Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('lscefa.quality.customers.destroy');

            // Rutas para cotizaciones (quotes)
            Route::resource('quotes', 'QuoteController', [
                'as' => 'lscefa.quality'
            ]);

            // Rutas adicionales para cotizaciones (PDF y carga de archivos)
            Route::get('quotes/{quote}/pdf', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'pdf'])->name('lscefa.quality.quotes.pdf');

            // Rutas para subir comprobante
            Route::get('quotes/{quote}/upload', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'uploadForm'])->name('lscefa.quality.quotes.upload');
            Route::post('quotes/{quote}/upload', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'upload'])->name('lscefa.quality.quotes.upload.post');

            // Ruta para iniciar procesos por terreno
            Route::post('quotes/{quote}/process/start', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'startProcess'])->name('lscefa.quality.process.start');

            // Rutas para procesos (igual que en proyecto_formativo)
            Route::get('processes', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'allProcessesIndex'])->name('lscefa.quality.processes.index');
            Route::get('processes/{process}', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'processShow'])->name('lscefa.quality.processes.show');
            Route::delete('processes/{process}', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'destroyProcess'])->name('lscefa.quality.processes.destroy');

            // Sección de Revisión de reportes técnicos
            Route::prefix('reviews')->name('lscefa.quality.reviews.')->group(function () {
                // Lista de análisis pendientes de revisión
                Route::get('/', [\Modules\LSCEFA\Http\Controllers\ReviewController::class, 'index'])
                    ->name('index');
                
                // Ver detalle de un análisis específico
                Route::get('/{id}', [\Modules\LSCEFA\Http\Controllers\ReviewController::class, 'show'])
                    ->name('show');
                    
                // Ruta de prueba para debugging
                Route::get('/test/boron', function() {
                    return view('lscefa::reviews.test_boron');
                })->name('test.boron');
                
                // Aprobar un análisis
                Route::post('/{id}/accept', [\Modules\LSCEFA\Http\Controllers\ReviewController::class, 'accept'])
                    ->name('accept')
                    ->middleware('lscefa.permission:lscefa.quality.reviews.accept');
                
                // Rechazar un análisis
                Route::post('/{id}/reject', [\Modules\LSCEFA\Http\Controllers\ReviewController::class, 'reject'])
                    ->name('reject')
                    ->middleware('lscefa.permission:lscefa.quality.reviews.reject');
                
                // Rutas para procesos (mantenidas por compatibilidad)
                Route::get('/process/{process}', [\Modules\LSCEFA\Http\Controllers\ReviewController::class, 'showProcess'])
                    ->name('process');
                Route::post('/process/{process}/accept', [\Modules\LSCEFA\Http\Controllers\ReviewController::class, 'acceptProcess'])
                    ->name('process.accept');
                Route::post('/process/{process}/reject', [\Modules\LSCEFA\Http\Controllers\ReviewController::class, 'rejectProcess'])
                    ->name('process.reject');
            });

            // Sección de creación de informes (Reportes)
            Route::prefix('reports')->name('lscefa.quality.reports.')->group(function () {
                // Listado de procesos en realización con filtro por código de ítem
                Route::get('/', [\Modules\LSCEFA\Http\Controllers\ReportsController::class, 'index'])
                    ->name('index');
                // Vista previa del informe por proceso
                Route::get('/{processId}', [\Modules\LSCEFA\Http\Controllers\ReportsController::class, 'show'])
                    ->name('show');
                // Descargar PDF del informe
                Route::get('/{processId}/pdf', [\Modules\LSCEFA\Http\Controllers\ReportsController::class, 'pdf'])
                    ->name('pdf');
            });

            
        }); // Cierre de Route::middleware(['auth', 'lscefa.role:lscefa.admin,lscefa.quality'])

        // Rutas de descarga de archivos
        // MÁS ESPECÍFICA PRIMERO: comunicación por quote_id
        Route::get('download/communication/{quote_id}/{filename}/{type?}', [\Modules\LSCEFA\Http\Controllers\FileDownloadController::class, 'downloadCommunication'])
            ->where(['quote_id' => '.*', 'filename' => '.*'])
            ->name('cefa.lscefa.download.communication');
        // Backward compatibility (by_quote antiguo)
        Route::get('download/communication/{quote_id}/{filename}', [\Modules\LSCEFA\Http\Controllers\FileDownloadController::class, 'downloadCommunication'])
            ->where(['quote_id' => '.*', 'filename' => '.*'])
            ->name('lscefa.download.communication.by_quote');
        // Legado: solo filename sin barras
        Route::get('download/communication/{filename}', [\Modules\LSCEFA\Http\Controllers\FileDownloadController::class, 'downloadCommunication'])
            ->where('filename', '[^\/]+')
            ->name('lscefa.download.communication');
            
        // Ruta para descargar comprobantes con parámetro opcional 'type'
        Route::get('download/comprobante/{quote_id}/{filename}/{type?}', [\Modules\LSCEFA\Http\Controllers\FileDownloadController::class, 'downloadComprobante'])
            ->where('quote_id', '.*')  // Acepta cualquier carácter en quote_id
            ->name('cefa.lscefa.download.comprobante');

        Route::middleware(['auth'])->group(function () {
            // Ruta para subir archivos (mantener esta ruta como está si es necesaria para usuarios no autenticados)
            Route::get('lscefa/quotes/upload/{id}', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'showUploadForm'])
                ->name('lscefa.quality.quotes.upload.form')
                ->middleware(['auth', 'can:lscefa.quality.quotes.upload']);
        });

        Route::middleware(['auth', 'lscefa.role:lscefa.technical'])->group(function () {
            // Rutas para Analisis de humedad
            Route::get('/technical/analyses/humidity', [HumidityAnalysisController::class, 'index'])->name('lscefa.technical.analyses.humidity.index');
            Route::get('/technical/analyses/humidity/{processId}/process', [HumidityAnalysisController::class, 'humidityAnalysis'])->name('lscefa.technical.analyses.humidity.process');
            Route::post('/technical/analyses/humidity/store', [HumidityAnalysisController::class, 'storeHumidityAnalysis'])->name('lscefa.technical.analyses.humidity.store');
            Route::get('/technical/analyses/humidity/{analysisId}/download', [HumidityAnalysisController::class, 'downloadHumidityReport'])->name('lscefa.technical.analyses.humidity.download');

            // Rutas para Análisis de Intercambio Catiónico
            Route::get('/technical/analyses/cationic', [CationicAnalysisController::class, 'index'])
                ->name('lscefa.technical.analyses.cationic.index')
                ->middleware('lscefa.permission:lscefa.technical.analyses.cationic.index');
            Route::get('/technical/analyses/cationic/process/{processId}/{serviceId}', [CationicAnalysisController::class, 'cationicAnalysis'])
                ->name('lscefa.technical.analyses.cationic.process')
                ->middleware('lscefa.permission:lscefa.technical.analyses.cationic.process');
            Route::post('/technical/analyses/cationic/store', [CationicAnalysisController::class, 'storeCationicAnalysis'])->name('lscefa.technical.analyses.cationic.store');
            Route::get('/technical/analyses/cationic/batch', [CationicAnalysisController::class, 'batchProcess'])->name('lscefa.technical.analyses.cationic.batch');
            Route::post('/technical/analyses/cationic/batch', [CationicAnalysisController::class, 'batchProcess'])
                ->name('lscefa.technical.analyses.cationic.batch.post')
                ->middleware('lscefa.permission:lscefa.technical.analyses.cationic.batch.post');
            Route::post('/technical/analyses/cationic/batch-store', [CationicAnalysisController::class, 'batchStore'])
                ->name('lscefa.technical.analyses.cationic.batch_store')
                ->middleware('lscefa.permission:lscefa.technical.analyses.cationic.batch_store');
            Route::get('/technical/analyses/cationic/{id}', [CationicAnalysisController::class, 'show'])->name('lscefa.technical.analyses.cationic.show');
            Route::get('/technical/analyses/cationic/{id}/edit', [CationicAnalysisController::class, 'edit'])->name('lscefa.technical.analyses.cationic.edit');
            Route::put('/technical/analyses/cationic/{id}', [CationicAnalysisController::class, 'update'])->name('lscefa.technical.analyses.cationic.update');
            Route::delete('/technical/analyses/cationic/{id}', [CationicAnalysisController::class, 'destroy'])->name('lscefa.technical.analyses.cationic.destroy');
            Route::get('/technical/analyses/cationic/{id}/report', [CationicAnalysisController::class, 'report'])->name('lscefa.technical.analyses.cationic.report');
            Route::get('/technical/analyses/cationic/{id}/edit-rejected', [CationicAnalysisController::class, 'editRejected'])->name('lscefa.technical.analyses.cationic.edit_rejected')
                ->middleware('lscefa.permission:lscefa.technical.analyses.cationic.edit_rejected');
            Route::post('/technical/analyses/cationic/{id}/update-rejected', [CationicAnalysisController::class, 'updateRejected'])->name('lscefa.technical.analyses.cationic.update_rejected')
                ->middleware('lscefa.permission:lscefa.technical.analyses.cationic.update_rejected');

            // Rutas para Análisis de Fósforo
            Route::get('/technical/analyses/phosphorus', [PhosphorusAnalysisController::class, 'index'])->name('lscefa.technical.analyses.phosphorus.index');
            Route::get('/technical/analyses/phosphorus/process/{processId}/{serviceId}', [PhosphorusAnalysisController::class, 'process'])->name('lscefa.technical.analyses.phosphorus.process');
            Route::post('/technical/analyses/phosphorus/store', [PhosphorusAnalysisController::class, 'storePhosphorusAnalysis'])->name('lscefa.technical.analyses.phosphorus.store');
            Route::get('/technical/analyses/phosphorus/batch', [PhosphorusAnalysisController::class, 'batchProcess'])->name('lscefa.technical.analyses.phosphorus.batch');
            Route::post('/technical/analyses/phosphorus/batch', [PhosphorusAnalysisController::class, 'batchProcess'])
                ->name('lscefa.technical.analyses.phosphorus.batch.post')
                ->middleware('lscefa.permission:lscefa.technical.analyses.phosphorus.batch.post');
            Route::post('/technical/analyses/phosphorus/batch-store', [PhosphorusAnalysisController::class, 'batchStore'])
                ->name('lscefa.technical.analyses.phosphorus.batch_store')
                ->middleware('lscefa.permission:lscefa.technical.analyses.phosphorus.batch_store');
            Route::get('/technical/analyses/phosphorus/{id}', [PhosphorusAnalysisController::class, 'show'])->name('lscefa.technical.analyses.phosphorus.show');
            Route::get('/technical/analyses/phosphorus/{id}/edit', [PhosphorusAnalysisController::class, 'edit'])->name('lscefa.technical.analyses.phosphorus.edit');
            Route::put('/technical/analyses/phosphorus/{id}', [PhosphorusAnalysisController::class, 'update'])->name('lscefa.technical.analyses.phosphorus.update');
            Route::delete('/technical/analyses/phosphorus/{id}', [PhosphorusAnalysisController::class, 'destroy'])->name('lscefa.technical.analyses.phosphorus.destroy');
            Route::get('/technical/analyses/phosphorus/{id}/report', [PhosphorusAnalysisController::class, 'report'])->name('lscefa.technical.analyses.phosphorus.report');

            // Rutas para Análisis de Bases Cambiables
            Route::get('/technical/analyses/exchangeable_bases', [ExchangeableBasesAnalysisController::class, 'index'])
                ->name('lscefa.technical.analyses.exchangeable_bases.index')
                ->middleware('lscefa.permission:lscefa.technical.analyses.exchangeable_bases.index');
            Route::get('/technical/analyses/exchangeable_bases/process/{processId}/{serviceId}', [ExchangeableBasesAnalysisController::class, 'process'])
                ->name('lscefa.technical.analyses.exchangeable_bases.process')
                ->middleware('lscefa.permission:lscefa.technical.analyses.exchangeable_bases.process');
            Route::post('/technical/analyses/exchangeable_bases/store', [ExchangeableBasesAnalysisController::class, 'storeExchangeableBasesAnalysis'])->name('lscefa.technical.analyses.exchangeable_bases.store');
            Route::get('/technical/analyses/exchangeable_bases/batch', [ExchangeableBasesAnalysisController::class, 'batchProcess'])->name('lscefa.technical.analyses.exchangeable_bases.batch');
            Route::post('/technical/analyses/exchangeable_bases/batch', [ExchangeableBasesAnalysisController::class, 'batchProcess'])
                ->name('lscefa.technical.analyses.exchangeable_bases.batch.post')
                ->middleware('lscefa.permission:lscefa.technical.analyses.exchangeable_bases.batch.post');
            Route::post('/technical/analyses/exchangeable_bases/batch-store', [ExchangeableBasesAnalysisController::class, 'batchStore'])
                ->name('lscefa.technical.analyses.exchangeable_bases.batch_store')
                ->middleware('lscefa.permission:lscefa.technical.analyses.exchangeable_bases.batch_store');
            Route::get('/technical/analyses/exchangeable_bases/{id}', [ExchangeableBasesAnalysisController::class, 'show'])->name('lscefa.technical.analyses.exchangeable_bases.show');
            Route::get('/technical/analyses/exchangeable_bases/{id}/edit', [ExchangeableBasesAnalysisController::class, 'edit'])->name('lscefa.technical.analyses.exchangeable_bases.edit');
            Route::put('/technical/analyses/exchangeable_bases/{id}', [ExchangeableBasesAnalysisController::class, 'update'])->name('lscefa.technical.analyses.exchangeable_bases.update');
            Route::delete('/technical/analyses/exchangeable_bases/{id}', [ExchangeableBasesAnalysisController::class, 'destroy'])->name('lscefa.technical.analyses.exchangeable_bases.destroy');
            Route::get('/technical/analyses/exchangeable_bases/{id}/report', [ExchangeableBasesAnalysisController::class, 'report'])->name('lscefa.technical.analyses.exchangeable_bases.report');

            // Rutas para Análisis de Azufre
            Route::get('/technical/analyses/sulfur', [SulfurAnalysisController::class, 'index'])->name('lscefa.technical.analyses.sulfur.index')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.index');
            Route::get('/technical/analyses/sulfur/process/{processId}/{serviceId}', [SulfurAnalysisController::class, 'process'])->name('lscefa.technical.analyses.sulfur.process')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.process');
            Route::post('/technical/analyses/sulfur/store', [SulfurAnalysisController::class, 'storeSulfurAnalysis'])->name('lscefa.technical.analyses.sulfur.store')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.store');
            Route::get('/technical/analyses/sulfur/batch', [SulfurAnalysisController::class, 'batchProcess'])->name('lscefa.technical.analyses.sulfur.batch')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.batch');
            Route::post('/technical/analyses/sulfur/batch', [SulfurAnalysisController::class, 'batchProcess'])
                ->name('lscefa.technical.analyses.sulfur.batch.post')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.batch.post');
            Route::post('/technical/analyses/sulfur/batch-store', [SulfurAnalysisController::class, 'batchStore'])
                ->name('lscefa.technical.analyses.sulfur.batch_store')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.batch_store');
            Route::get('/technical/analyses/sulfur/{id}', [SulfurAnalysisController::class, 'show'])->name('lscefa.technical.analyses.sulfur.show')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.show');
            Route::get('/technical/analyses/sulfur/{id}/edit', [SulfurAnalysisController::class, 'edit'])->name('lscefa.technical.analyses.sulfur.edit')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.edit');
            Route::put('/technical/analyses/sulfur/{id}', [SulfurAnalysisController::class, 'update'])->name('lscefa.technical.analyses.sulfur.update')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.update');
            Route::delete('/technical/analyses/sulfur/{id}', [SulfurAnalysisController::class, 'destroy'])->name('lscefa.technical.analyses.sulfur.destroy')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.destroy');
            Route::get('/technical/analyses/sulfur/{id}/report', [SulfurAnalysisController::class, 'report'])->name('lscefa.technical.analyses.sulfur.report')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.report');
            Route::get('/technical/analyses/sulfur/{id}/edit-rejected', [SulfurAnalysisController::class, 'editRejected'])->name('lscefa.technical.analyses.sulfur.edit_rejected')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.edit_rejected');
            Route::post('/technical/analyses/sulfur/{id}/update-rejected', [SulfurAnalysisController::class, 'updateRejected'])->name('lscefa.technical.analyses.sulfur.update_rejected')
                ->middleware('lscefa.permission:lscefa.technical.analyses.sulfur.update_rejected');

            // Rutas para Análisis de Boro
            Route::get('/technical/analyses/boron', [BoronAnalysisController2::class, 'index'])->name('lscefa.technical.analyses.boron.index');
            Route::get('/technical/analyses/boron/process/{processId}/{serviceId}', [BoronAnalysisController2::class, 'process'])->name('lscefa.technical.analyses.boron.process');
            Route::post('/technical/analyses/boron/store', [BoronAnalysisController2::class, 'storeBoronAnalysis'])->name('lscefa.technical.analyses.boron.store');
            Route::get('/technical/analyses/boron/batch', [BoronAnalysisController2::class, 'batchProcess'])->name('lscefa.technical.analyses.boron.batch');
            Route::post('/technical/analyses/boron/batch', [BoronAnalysisController2::class, 'batchProcess'])
                ->name('lscefa.technical.analyses.boron.batch.post')
                ->middleware('lscefa.permission:lscefa.technical.analyses.boron.batch.post');
            Route::post('/technical/analyses/boron/batch-store', [BoronAnalysisController2::class, 'batchStore'])
                ->name('lscefa.technical.analyses.boron.batch_store')
                ->middleware('lscefa.permission:lscefa.technical.analyses.boron.batch_store');
            Route::get('/technical/analyses/boron/{id}', [BoronAnalysisController2::class, 'show'])->name('lscefa.technical.analyses.boron.show');
            Route::get('/technical/analyses/boron/{id}/edit', [BoronAnalysisController2::class, 'edit'])->name('lscefa.technical.analyses.boron.edit');
            Route::put('/technical/analyses/boron/{id}', [BoronAnalysisController2::class, 'update'])->name('lscefa.technical.analyses.boron.update');
            Route::delete('/technical/analyses/boron/{id}', [BoronAnalysisController2::class, 'destroy'])->name('lscefa.technical.analyses.boron.destroy');
            Route::get('/technical/analyses/boron/{id}/report', [BoronAnalysisController2::class, 'report'])->name('lscefa.technical.analyses.boron.report');
            Route::get('/technical/analyses/boron/{id}/edit-rejected', [BoronAnalysisController2::class, 'editRejected'])->name('lscefa.technical.analyses.boron.edit_rejected')
                ->middleware('lscefa.permission:lscefa.technical.analyses.boron.edit_rejected');
            Route::post('/technical/analyses/boron/{id}/update-rejected', [BoronAnalysisController2::class, 'updateRejected'])->name('lscefa.technical.analyses.boron.update_rejected')
                ->middleware('lscefa.permission:lscefa.technical.analyses.boron.update_rejected');
            Route::get('/technical/analyses/boron/{analysisId}/download', [BoronAnalysisController2::class, 'downloadBoronReport'])->name('lscefa.technical.analyses.boron.download');

            // Rutas para Análisis de Carbono
            Route::get('/technical/analyses/carbon', [CarbonoAnalysisController::class, 'index'])->name('lscefa.technical.analyses.carbon.index');
            Route::get('/technical/analyses/carbon/process/{processId}/{serviceId}', [CarbonoAnalysisController::class, 'carbonoAnalysis'])->name('lscefa.technical.analyses.carbon.process');
            Route::post('/technical/analyses/carbon/store', [CarbonoAnalysisController::class, 'storeCarbonoAnalysis'])->name('lscefa.technical.analyses.carbon.store');
            Route::get('/technical/analyses/carbon/batch', [CarbonoAnalysisController::class, 'batchProcess'])->name('lscefa.technical.analyses.carbon.batch');
            Route::post('/technical/analyses/carbon/batch', [CarbonoAnalysisController::class, 'batchProcess'])
                ->name('lscefa.technical.analyses.carbon.batch.post')
                ->middleware('lscefa.permission:lscefa.technical.analyses.carbon.batch.post');
            Route::post('/technical/analyses/carbon/batch-store', [CarbonoAnalysisController::class, 'batchStore'])
                ->name('lscefa.technical.analyses.carbon.batch_store')
                ->middleware('lscefa.permission:lscefa.technical.analyses.carbon.batch_store');
            Route::get('/technical/analyses/carbon/{id}', [CarbonoAnalysisController::class, 'show'])->name('lscefa.technical.analyses.carbon.show');
            Route::get('/technical/analyses/carbon/{id}/edit', [CarbonoAnalysisController::class, 'edit'])->name('lscefa.technical.analyses.carbon.edit');
            Route::put('/technical/analyses/carbon/{id}', [CarbonoAnalysisController::class, 'update'])->name('lscefa.technical.analyses.carbon.update');
            Route::delete('/technical/analyses/carbon/{id}', [CarbonoAnalysisController::class, 'destroy'])->name('lscefa.technical.analyses.carbon.destroy');
            Route::get('/technical/analyses/carbon/{id}/report', [CarbonoAnalysisController::class, 'report'])->name('lscefa.technical.analyses.carbon.report');

            // Rutas para Análisis de Micronutrientes
            Route::get('/technical/analyses/micronutrients', [MicronutrientsAnalysisController2::class, 'index'])->name('lscefa.technical.analyses.micronutrients.index');
            Route::get('/technical/analyses/micronutrients/process/{processId}/{serviceId}', [MicronutrientsAnalysisController2::class, 'process'])->name('lscefa.technical.analyses.micronutrients.process');
            Route::post('/technical/analyses/micronutrients/store', [MicronutrientsAnalysisController2::class, 'storeMicronutrientsAnalysis'])->name('lscefa.technical.analyses.micronutrients.store');
            Route::get('/technical/analyses/micronutrients/batch', [MicronutrientsAnalysisController2::class, 'batchProcess'])->name('lscefa.technical.analyses.micronutrients.batch');
            Route::post('/technical/analyses/micronutrients/batch', [MicronutrientsAnalysisController2::class, 'batchProcess'])
                ->name('lscefa.technical.analyses.micronutrients.batch.post')
                ->middleware('lscefa.permission:lscefa.technical.analyses.micronutrients.batch.post');
            Route::post('/technical/analyses/micronutrients/batch-store', [MicronutrientsAnalysisController2::class, 'batchStore'])
                ->name('lscefa.technical.analyses.micronutrients.batch_store')
                ->middleware('lscefa.permission:lscefa.technical.analyses.micronutrients.batch_store');
            Route::get('/technical/analyses/micronutrients/{id}', [MicronutrientsAnalysisController2::class, 'show'])->name('lscefa.technical.analyses.micronutrients.show');
            Route::get('/technical/analyses/micronutrients/{id}/edit', [MicronutrientsAnalysisController2::class, 'edit'])->name('lscefa.technical.analyses.micronutrients.edit');
            Route::put('/technical/analyses/micronutrients/{id}', [MicronutrientsAnalysisController2::class, 'update'])->name('lscefa.technical.analyses.micronutrients.update');
            Route::delete('/technical/analyses/micronutrients/{id}', [MicronutrientsAnalysisController2::class, 'destroy'])->name('lscefa.technical.analyses.micronutrients.destroy');
            Route::get('/technical/analyses/micronutrients/{id}/report', [MicronutrientsAnalysisController2::class, 'report'])->name('lscefa.technical.analyses.micronutrients.report');

            // Rutas para Análisis de Acidez
            Route::get('/technical/analyses/acidity', [AcidezAnalysisController::class, 'index'])->name('lscefa.technical.analyses.acidity.index');
            Route::get('/process/{processId}', [AcidezAnalysisController::class, 'acidityAnalysis'])->name('lscefa.technical.analyses.acidity.process');
            Route::post('/store', [AcidezAnalysisController::class, 'storeAcidezAnalysis'])->name('lscefa.technical.analyses.acidity.store');
            Route::post('/technical/analyses/acidity/batch-process', [AcidezAnalysisController::class, 'batchProcess'])->name('lscefa.technical.analyses.acidity.batchProcess');
            Route::post('/technical/analyses/acidity/batch-store', [AcidezAnalysisController::class, 'batchStore'])->name('lscefa.technical.analyses.acidity.batchStore');
        });

        // Ruta para que el header global funcione correctamente en el módulo LSCEFA
        Route::get('/lscefa/home', [LSCEFAController::class, 'index'])->name('cefa.home');
    }); // Cierre de Route::prefix('lscefa')

    }); // Cierre de Route::middleware(['lang'])

        // Ruta para descargar el manual técnico (accesible para todos los usuarios autenticados)
        Route::middleware(['auth'])->group(function () {
            Route::get('/download/manual-tecnico', function () {
                $path = module_path('LSCEFA', 'public/Manual Tecnico.pdf');
                if (file_exists($path)) {
                    return response()->download($path, 'Manual Tecnico LSCEFA.pdf');
                }
                abort(404, 'Manual técnico no encontrado');
            })->name('lscefa.download.manual');
        });
