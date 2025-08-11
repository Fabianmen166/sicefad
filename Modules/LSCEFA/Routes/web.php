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
use Modules\LSCEFA\Http\Controllers\CarbonoAnalysisController;
use Modules\LSCEFA\Http\Controllers\CationicAnalysisController;
use Modules\LSCEFA\Http\Controllers\PhosphorusAnalysisController;


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
        });

        Route::middleware(['auth'])->group(function () {
            // Rutas de descarga de archivos
            Route::get('communication-file/{filename}', [QuoteController::class, 'downloadCommunicationFile'])
                ->name('lscefa.communication_file.download');
                
            Route::get('comprobante-file/{quote_id}/{filename}', [QuoteController::class, 'downloadComprobante'])
                ->name('lscefa.comprobante_file.download');
        });

        // Ruta para subir archivos (mantener esta ruta como está si es necesaria para usuarios no autenticados)
        Route::get('lscefa/quotes/upload/{id}', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'showUploadForm'])
            ->name('lscefa.quality.quotes.upload.form')
            ->middleware(['auth', 'can:lscefa.quality.quotes.upload']);

         });

        Route::middleware(['auth', 'lscefa.role:lscefa.technical'])->group(function () {
            // Rutas para Analisis de humedad
            Route::get('/technical/analyses/humidity', [HumidityAnalysisController::class, 'index'])->name('lscefa.technical.analyses.humidity.index');
            Route::get('/technical/analyses/humidity/process/{processId}/{serviceId}', [HumidityAnalysisController::class, 'humidityAnalysis'])->name('lscefa.technical.analyses.humidity.process');
            Route::post('/technical/analyses/humidity/store', [HumidityAnalysisController::class, 'storeHumidityAnalysis'])->name('lscefa.technical.analyses.humidity.store');

            // Rutas para Análisis de Intercambio Catiónico
            Route::get('/technical/analyses/cationic', [CationicAnalysisController::class, 'index'])->name('lscefa.technical.analyses.cationic.index');
            Route::get('/technical/analyses/cationic/process/{processId}/{serviceId}', [CationicAnalysisController::class, 'cationicAnalysis'])->name('lscefa.technical.analyses.cationic.process');
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

        // Rutas para Análisis de Carbono Orgánico
        Route::get('/technical/analyses/carbon', [CarbonoAnalysisController::class, 'index'])->name('lscefa.technical.analyses.carbon.index');
        Route::get('/technical/analyses/carbon/process/{processId}/{serviceId}', [CarbonoAnalysisController::class, 'carbonAnalysis'])->name('lscefa.technical.analyses.carbon.process');
        Route::post('/technical/analyses/carbon/store', [CarbonoAnalysisController::class, 'storeCarbonoAnalysis'])->name('lscefa.technical.analyses.carbon.store');
    //});
     // Route::post('/admin/units/productive_units/environment_pus/store', [UnitController::class, 'environment_pus_store'])->name('sica.admin.units.productive_units.environment_pus.store'); /* Registrar asociación de ambiente y unidad productiva (Administrador) */
      

        // Ruta para que el header global funcione correctamente en el módulo LSCEFA
        Route::get('/lscefa/home', [LSCEFAController::class, 'index'])->name('cefa.home');
    });

         
});