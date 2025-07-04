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
use Modules\LSCEFA\Http\Middleware\CheckLSCEFARole;

Route::middleware(['lang'])->group(function(){
    Route::prefix('lscefa')->group(function () {
        // Rutas públicas
        Route::get('/index', [LSCEFAController::class, 'index'])->name('cefa.lscefa.index');

        // Rutas protegidas por rol
        Route::middleware(['auth', 'lscefa.role:lscefa.admin'])->group(function () {
            Route::get('/admin/welcome', [LSCEFAController::class, 'admin'])->name('lscefa.admin.welcome');
            Route::get('/admin/config', [LSCEFAController::class, 'config'])->name('lscefa.admin.config');
        });

        Route::middleware(['auth', 'lscefa.role:lscefa.intern'])->group(function () {
            Route::get('/intern/panelpas', [LSCEFAController::class, 'intern'])->name('lscefa.intern.panelpas');
            Route::get('/intern/tasks', [LSCEFAController::class, 'tasks'])->name('lscefa.intern.tasks');
        });

        Route::middleware(['auth', 'lscefa.role:lscefa.technical'])->group(function () {
            Route::get('/technical/panel', [LSCEFAController::class, 'technical'])->name('lscefa.technical.panel');
            Route::get('/technical/samples', [LSCEFAController::class, 'samples'])->name('lscefa.technical.samples');
 
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
            Route::get('quotes/upload/{quote}', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'showUploadForm'])->name('lscefa.quality.quotes.upload.form');
            Route::post('quotes/upload/{quote}', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'upload'])->name('lscefa.quality.quotes.upload');
        });

        
Route::get('lscefa/clientes/buscar', [\Modules\LSCEFA\Http\Controllers\CustomerController::class, 'searchAjax'])->name('lscefa.quality.customers.searchAjax');
Route::get('lscefa/servicios/buscar', [\Modules\LSCEFA\Http\Controllers\ServiceController::class, 'searchAjax'])->name('lscefa.quality.services.searchAjax');
Route::get('lscefa/paquetes/buscar', [\Modules\LSCEFA\Http\Controllers\ServicePackageController::class, 'searchAjax'])->name('lscefa.quality.service_packages.searchAjax');
Route::get('lscefa/cotizaciones/buscar', [\Modules\LSCEFA\Http\Controllers\QuoteController::class, 'searchAjax'])->name('lscefa.quality.quotes.searchAjax');

        // Ruta para que el header global funcione correctamente en el módulo LSCEFA
        Route::get('/lscefa/home', [LSCEFAController::class, 'index'])->name('cefa.home');
    });
});