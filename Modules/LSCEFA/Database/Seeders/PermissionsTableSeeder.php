<?php

namespace Modules\LSCEFA\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SICA\Entities\App;
use Modules\SICA\Entities\Permission;
use Modules\SICA\Entities\Role;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Definir arreglos de PERMISOS que van ser asignados a los ROLES
        $permissions_admin = [];
        $permissions_quality = [];
        $permissions_technical = [];
        $permissions_intern = [];

        // Consultar la aplicación LSCEFA una sola vez
        $app = App::where('name', 'LSCEFA')->first();

        // Permisos Rol (Administrador)
        $permissions_admin = [];

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.index'], [
            'name' => 'Vista de configuración (Administrador)',
            'description' => 'Configuración de parámetros generales y testeo de impresión POS',
            'description_english' => 'Configuration of general parameters and POS printing test',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.welcome'], [
            'name' => 'Panel de administrador',
            'description' => 'Acceso al panel de administración de LSCEFA',
            'description_english' => 'Access to the LSCEFA administration panel',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        // Permiso para la gestión de usuarios
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.users.index'], [
            'name' => 'Gestión de Usuarios',
            'description' => 'Puede ver el listado de usuarios',
            'description_english' => 'Can view the user list',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        // Permisos CRUD de Gestión de Usuarios (Admin)
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.users.create'], [
            'name' => 'Crear Usuario (Admin)',
            'description' => 'Puede acceder al formulario de creación de usuarios',
            'description_english' => 'Can access user creation form',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.users.store'], [
            'name' => 'Registrar Usuario (Admin)',
            'description' => 'Puede registrar un nuevo usuario',
            'description_english' => 'Can store a new user',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.users.edit'], [
            'name' => 'Editar Usuario (Admin)',
            'description' => 'Puede acceder al formulario de edición de usuarios',
            'description_english' => 'Can access user edit form',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.users.update'], [
            'name' => 'Actualizar Usuario (Admin)',
            'description' => 'Puede actualizar un usuario',
            'description_english' => 'Can update a user',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.users.destroy'], [
            'name' => 'Eliminar Usuario (Admin)',
            'description' => 'Puede eliminar un usuario',
            'description_english' => 'Can delete a user',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        // Permisos para Revisión de reportes (solo Admin)
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.reviews.index'], [
            'name' => 'Revisiones - Ver listado (Admin)',
            'description' => 'Puede ver el listado de reportes pendientes de revisión',
            'description_english' => 'Can view review pending list',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.reviews.process'], [
            'name' => 'Revisiones - Ver detalle de proceso (Admin)',
            'description' => 'Puede ver el detalle de un proceso en revisión',
            'description_english' => 'Can view process details in review',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.reviews.process.accept'], [
            'name' => 'Revisiones - Aceptar proceso (Admin)',
            'description' => 'Puede aceptar un proceso en revisión',
            'description_english' => 'Can accept a process under review',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.reviews.process.reject'], [
            'name' => 'Revisiones - Rechazar proceso (Admin)',
            'description' => 'Puede rechazar un proceso en revisión',
            'description_english' => 'Can reject a process under review',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.reviews.show'], [
            'name' => 'Revisiones - Ver detalle (Admin)',
            'description' => 'Puede ver el detalle de un reporte para revisión',
            'description_english' => 'Can view review detail',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.reviews.accept'], [
            'name' => 'Revisiones - Aceptar (Admin)',
            'description' => 'Puede aceptar un reporte en revisión',
            'description_english' => 'Can accept a review item',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.reviews.reject'], [
            'name' => 'Revisiones - Rechazar (Admin)',
            'description' => 'Puede rechazar un reporte en revisión',
            'description_english' => 'Can reject a review item',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        // Duplicados con prefijo QUALITY para middleware que mapea por nombre de ruta
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.quality.reviews.index'], [
            'name' => 'Revisiones - Ver listado (Quality route name)',
            'description' => 'Habilita acceso por nombre de ruta lscefa.quality.reviews.index',
            'description_english' => 'Enable access by route name',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.quality.reviews.show'], [
            'name' => 'Revisiones - Ver detalle (Quality route name)',
            'description' => 'Habilita acceso por nombre de ruta lscefa.quality.reviews.show',
            'description_english' => 'Enable access by route name',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.quality.reviews.accept'], [
            'name' => 'Revisiones - Aceptar (Quality route name)',
            'description' => 'Habilita acceso por nombre de ruta lscefa.quality.reviews.accept',
            'description_english' => 'Enable access by route name',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.quality.reviews.reject'], [
            'name' => 'Revisiones - Rechazar (Quality route name)',
            'description' => 'Habilita acceso por nombre de ruta lscefa.quality.reviews.reject',
            'description_english' => 'Enable access by route name',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        // Permisos para la nueva sección de Reportes (solo Admin, usando nombres de ruta quality)
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.quality.reports.index'], [
            'name' => 'Reportes - Listado (Admin)',
            'description' => 'Puede ver el listado de procesos en realización (Informes)',
            'description_english' => 'Can view reports list (in-progress processes)',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.quality.reports.show'], [
            'name' => 'Reportes - Ver detalle (Admin)',
            'description' => 'Puede ver la vista previa del informe por proceso',
            'description_english' => 'Can view report preview for a process',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        // Permiso para descargar el PDF del informe (solo Admin)
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.quality.reports.pdf'], [
            'name' => 'Reportes - Descargar PDF (Admin)',
            'description' => 'Puede descargar el informe en PDF',
            'description_english' => 'Can download the report as PDF',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        // Permiso para ver listado del Historial de Procesos (solo Admin)
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.process_history.index'], [
            'name' => 'Historial de Procesos - Listado (Admin)',
            'description' => 'Puede ver el listado de procesos para acceder a su historial',
            'description_english' => 'Can view processes list to access their history',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        // Permiso para ver el Historial de Proceso (solo Admin)
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.process_history.show'], [
            'name' => 'Historial de Proceso - Ver (Admin)',
            'description' => 'Puede ver el historial completo del proceso (cotización -> análisis -> informe)',
            'description_english' => 'Can view full process history (quote -> analyses -> report)',
            'app_id' => $app->id
        ]);
        $permissions_admin[] = $permission->id;

        $rol_admin = Role::where('slug', 'lscefa.admin')->first();
        $rol_admin->permissions()->syncWithoutDetaching($permissions_admin);

        // Permisos Rol (Pasante)
        $permissions_intern = [];

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.intern.panelpas'], [
            'name' => 'Panel de pasante',
            'description' => 'Acceso al panel de pasantes para tareas asignadas',
            'description_english' => 'Access to the intern panel for assigned tasks',
            'app_id' => $app->id
        ]);
        $permissions_intern[] = $permission->id;

        $rol_intern = Role::where('slug', 'lscefa.intern')->first();
        $rol_intern->permissions()->syncWithoutDetaching($permissions_intern);

        // Permisos Rol (Personal Técnico)
        $permissions_technical = [];

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.technical.panel'], [
            'name' => 'Panel técnico',
            'description' => 'Acceso al panel de personal técnico',
            'description_english' => 'Access to technical staff panel',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permission->id;

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.technical.samples'], [
            'name' => 'Gestión de muestras',
            'description' => 'Acceso a la gestión de muestras',
            'description_english' => 'Access to samples management',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permission->id;

        // Permiso para ver el listado de análisis técnicos
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.index'], [
            'name' => 'Ver listado de Análisis Técnicos',
            'description' => 'Puede ver el listado de procesos pendientes para análisis técnico',
            'description_english' => 'Can view the list of pending processes for technical analysis',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permission->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        // Permisos para Analisis de humedad
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.humidity.index'], [
            'name' => 'Ver listado de Análisis de Humedad (Technical)',
            'description' => 'Puede ver el listado de análisis de humedad (technical)',
            'description_english' => 'Can view humidity analysis list (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.humidity.process'], [
            'name' => 'Ver listado de Análisis de Humedad (Technical)',
            'description' => 'Puede ver el listado de análisis de humedad (technical)',
            'description_english' => 'Can view humidity analysis list (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        // Permisos para ingresar los resultados de humedad
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.humidity.store'], [
            'name' => 'guardar los Análisis de Humedad (Technical)',
            'description' => 'Puede guardar  los análisis de humedad (technical)',
            'description_english' => 'Can process humidity analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        // Permisos para Análisis de Carbono Orgánico
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.carbon.index'], [
            'name' => 'Ver listado de Análisis de Carbono Orgánico (Technical)',
            'description' => 'Puede ver el listado de análisis de carbono orgánico (technical)',
            'description_english' => 'Can view carbon organic analysis list (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.carbon.process'], [
            'name' => 'Procesar Análisis de Carbono Orgánico (Technical)',
            'description' => 'Puede procesar los análisis de carbono orgánico (technical)',
            'description_english' => 'Can process carbon organic analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;
        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.carbon.store'], [
            'name' => 'Guardar Análisis de Carbono Orgánico (Technical)',
            'description' => 'Puede guardar los análisis de carbono orgánico (technical)',
            'description_english' => 'Can store carbon organic analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        // Permisos para Análisis Acidez
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.acidity.index'], [
            'name' => 'Ver listado de Análisis de Acidez (Technical)',
            'description' => 'Puede ver el listado de análisis de acidez (technical)',
            'description_english' => 'Can view acidity analysis list (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;
        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.acidity.process'], [
            'name' => 'Procesar Análisis de Acidez (Technical)',
            'description' => 'Puede procesar los análisis de acidez (technical)',
            'description_english' => 'Can process acidity analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;
        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.acidity.store'], [
            'name' => 'Guardar Análisis de Acidez (Technical)',
            'description' => 'Puede guardar los análisis de acidez (technical)',
            'description_english' => 'Can store acidity analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;
        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        // permisos para lotes de acidez

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.acidity.batchProcess'], [
            'name' => 'Procesar Análisis de Acidez (Technical)',
            'description' => 'Puede procesar los análisis de acidez (technical)',
            'description_english' => 'Can process acidity analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;
        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.acidity.batchStore'], [
            'name' => 'Guardar procesamiento por lotes de Análisis de Acidez (Technical)',
            'description' => 'Puede guardar el procesamiento por lotes de análisis de acidez (technical)',
            'description_english' => 'Can save batch processing of acidity analyses (technical)',
            'app_id' => $app->id
        ]); 
        $permissions_technical[] = $permision->id;
        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);




        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.cationic.edit'], [
            'name' => 'Editar Análisis de Intercambio Catiónico (Technical)',
            'description' => 'Puede editar análisis de intercambio catiónico (technical)',
            'description_english' => 'Can edit cationic exchange analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.cationic.update'], [
            'name' => 'Actualizar Análisis de Intercambio Catiónico (Technical)',
            'description' => 'Puede actualizar análisis de intercambio catiónico (technical)',
            'description_english' => 'Can update cationic exchange analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.cationic.destroy'], [
            'name' => 'Eliminar Análisis de Intercambio Catiónico (Technical)',
            'description' => 'Puede eliminar análisis de intercambio catiónico (technical)',
            'description_english' => 'Can delete cationic exchange analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.cationic.show'], [
            'name' => 'Ver detalle de Análisis de Intercambio Catiónico (Technical)',
            'description' => 'Puede ver el detalle de análisis de intercambio catiónico (technical)',
            'description_english' => 'Can view cationic exchange analysis details (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.cationic.report'], [
            'name' => 'Generar reporte de Análisis de Intercambio Catiónico (Technical)',
            'description' => 'Puede generar reportes de análisis de intercambio catiónico (technical)',
            'description_english' => 'Can generate cationic exchange analysis reports (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        // Permisos para procesamiento por lotes de análisis de intercambio catiónico
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.cationic.batch'], [
            'name' => 'Procesamiento por lotes de Análisis de Intercambio Catiónico (Technical)',
            'description' => 'Puede procesar múltiples análisis de intercambio catiónico por lotes (technical)',
            'description_english' => 'Can process multiple cationic exchange analyses in batches (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.cationic.batch.post'], [
            'name' => 'Acceso al formulario de procesamiento por lotes de Análisis de Intercambio Catiónico (Technical)',
            'description' => 'Puede acceder al formulario de procesamiento por lotes de análisis de intercambio catiónico (technical)',
            'description_english' => 'Can access the batch processing form for cationic exchange analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.cationic.batch_store'], [
            'name' => 'Guardar procesamiento por lotes de Análisis de Intercambio Catiónico (Technical)',
            'description' => 'Puede guardar el procesamiento por lotes de análisis de intercambio catiónico (technical)',
            'description_english' => 'Can save batch processing of cationic exchange analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        // Permisos para Análisis de Fósforo
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.index'], [
            'name' => 'Ver listado de Análisis de Fósforo (Technical)',
            'description' => 'Puede ver el listado de análisis de fósforo (technical)',
            'description_english' => 'Can view phosphorus analysis list (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.process'], [
            'name' => 'Procesar Análisis de Fósforo (Technical)',
            'description' => 'Puede procesar análisis de fósforo (technical)',
            'description_english' => 'Can process phosphorus analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.store'], [
            'name' => 'Guardar Análisis de Fósforo (Technical)',
            'description' => 'Puede guardar análisis de fósforo (technical)',
            'description_english' => 'Can save phosphorus analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.edit'], [
            'name' => 'Editar Análisis de Fósforo (Technical)',
            'description' => 'Puede editar análisis de fósforo (technical)',
            'description_english' => 'Can edit phosphorus analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.update'], [
            'name' => 'Actualizar Análisis de Fósforo (Technical)',
            'description' => 'Puede actualizar análisis de fósforo (technical)',
            'description_english' => 'Can update phosphorus analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.destroy'], [
            'name' => 'Eliminar Análisis de Fósforo (Technical)',
            'description' => 'Puede eliminar análisis de fósforo (technical)',
            'description_english' => 'Can delete phosphorus analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.show'], [
            'name' => 'Ver detalle de Análisis de Fósforo (Technical)',
            'description' => 'Puede ver el detalle de análisis de fósforo (technical)',
            'description_english' => 'Can view phosphorus analysis details (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.report'], [
            'name' => 'Generar reporte de Análisis de Fósforo (Technical)',
            'description' => 'Puede generar reportes de análisis de fósforo (technical)',
            'description_english' => 'Can generate phosphorus analysis reports (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        // Permisos para procesamiento por lotes de análisis de fósforo
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.batch'], [
            'name' => 'Procesamiento por lotes de Análisis de Fósforo (Technical)',
            'description' => 'Puede procesar múltiples análisis de fósforo por lotes (technical)',
            'description_english' => 'Can process multiple phosphorus analyses in batches (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.batch.post'], [
            'name' => 'Acceso al formulario de procesamiento por lotes de Análisis de Fósforo (Technical)',
            'description' => 'Puede acceder al formulario de procesamiento por lotes de análisis de fósforo (technical)',
            'description_english' => 'Can access the batch processing form for phosphorus analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.phosphorus.batch_store'], [
            'name' => 'Guardar procesamiento por lotes de Análisis de Fósforo (Technical)',
            'description' => 'Puede guardar el procesamiento por lotes de análisis de fósforo (technical)',
            'description_english' => 'Can save batch processing of phosphorus analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        // Permisos para Análisis de Bases Cambiables
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.index'], [
            'name' => 'Ver listado de Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede ver el listado de análisis de bases cambiables (technical)',
            'description_english' => 'Can view exchangeable bases analysis list (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.process'], [
            'name' => 'Procesar Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede procesar análisis de bases cambiables (technical)',
            'description_english' => 'Can process exchangeable bases analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.store'], [
            'name' => 'Guardar Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede guardar análisis de bases cambiables (technical)',
            'description_english' => 'Can save exchangeable bases analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.edit'], [
            'name' => 'Editar Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede editar análisis de bases cambiables (technical)',
            'description_english' => 'Can edit exchangeable bases analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.update'], [
            'name' => 'Actualizar Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede actualizar análisis de bases cambiables (technical)',
            'description_english' => 'Can update exchangeable bases analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.destroy'], [
            'name' => 'Eliminar Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede eliminar análisis de bases cambiables (technical)',
            'description_english' => 'Can delete exchangeable bases analysis (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.show'], [
            'name' => 'Ver detalle de Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede ver el detalle de análisis de bases cambiables (technical)',
            'description_english' => 'Can view exchangeable bases analysis details (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.report'], [
            'name' => 'Generar reporte de Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede generar reportes de análisis de bases cambiables (technical)',
            'description_english' => 'Can generate exchangeable bases analysis reports (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        // Permisos para procesamiento por lotes de análisis de bases cambiables
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.batch'], [
            'name' => 'Procesamiento por lotes de Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede procesar múltiples análisis de bases cambiables por lotes (technical)',
            'description_english' => 'Can process multiple exchangeable bases analyses in batches (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.batch.post'], [
            'name' => 'Acceso al formulario de procesamiento por lotes de Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede acceder al formulario de procesamiento por lotes de análisis de bases cambiables (technical)',
            'description_english' => 'Can access the batch processing form for exchangeable bases analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.exchangeable_bases.batch_store'], [
            'name' => 'Guardar procesamiento por lotes de Análisis de Bases Cambiables (Technical)',
            'description' => 'Puede guardar el procesamiento por lotes de análisis de bases cambiables (technical)',
            'description_english' => 'Can save batch processing of exchangeable bases analyses (technical)',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;

        $rol_technical = Role::where('slug', 'lscefa.technical')->first();
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);

        // Permisos Rol (Gestión de Calidad)
        $permissions_quality = [];

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.quality.dashboard'], [
            'name' => 'Panel de Gestión de Calidad',
            'description' => 'Acceso al panel de gestión de calidad',
            'description_english' => 'Access to the quality management panel',
            'app_id' => $app->id
        ]);
        $permissions_quality[] = $permission->id;

        $rol_quality = Role::where('slug', 'lscefa.quality')->first();
        $rol_quality->permissions()->syncWithoutDetaching($permissions_quality);

        // Permisos CRUD Tipos de Cliente (estructura igual a CAFETO)
        $permisos_admin_customer_types = [];
        $permisos_quality_customer_types = [];
        // index
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customer_types.index'], [
            'name' => 'Ver listado de Tipos de Cliente (Admin)',
            'description' => 'Puede ver el listado de tipos de cliente (admin)',
            'description_english' => 'Can view customer types list (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customer_types[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.customer_types.index'], [
            'name' => 'Ver listado de Tipos de Cliente (Quality)',
            'description' => 'Puede ver el listado de tipos de cliente (quality)',
            'description_english' => 'Can view customer types list (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customer_types[] = $perm->id;
        // create
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customer_types.create'], [
            'name' => 'Crear Tipo de Cliente (Admin)',
            'description' => 'Puede acceder al formulario de creación de tipos de cliente (admin)',
            'description_english' => 'Can access customer type creation form (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customer_types[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.customer_types.create'], [
            'name' => 'Crear Tipo de Cliente (Quality)',
            'description' => 'Puede acceder al formulario de creación de tipos de cliente (quality)',
            'description_english' => 'Can access customer type creation form (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customer_types[] = $perm->id;
        // store
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customer_types.store'], [
            'name' => 'Registrar Tipo de Cliente (Admin)',
            'description' => 'Puede registrar un nuevo tipo de cliente (admin)',
            'description_english' => 'Can store a new customer type (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customer_types[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.customer_types.store'], [
            'name' => 'Registrar Tipo de Cliente (Quality)',
            'description' => 'Puede registrar un nuevo tipo de cliente (quality)',
            'description_english' => 'Can store a new customer type (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customer_types[] = $perm->id;
        // edit
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customer_types.edit'], [
            'name' => 'Editar Tipo de Cliente (Admin)',
            'description' => 'Puede acceder al formulario de edición de tipos de cliente (admin)',
            'description_english' => 'Can access customer type edit form (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customer_types[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.customer_types.edit'], [
            'name' => 'Editar Tipo de Cliente (Quality)',
            'description' => 'Puede acceder al formulario de edición de tipos de cliente (quality)',
            'description_english' => 'Can access customer type edit form (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customer_types[] = $perm->id;
        // update
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customer_types.update'], [
            'name' => 'Actualizar Tipo de Cliente (Admin)',
            'description' => 'Puede actualizar un tipo de cliente (admin)',
            'description_english' => 'Can update a customer type (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customer_types[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.customer_types.update'], [
            'name' => 'Actualizar Tipo de Cliente (Quality)',
            'description' => 'Puede actualizar un tipo de cliente (quality)',
            'description_english' => 'Can update a customer type (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customer_types[] = $perm->id;
        // destroy
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customer_types.destroy'], [
            'name' => 'Eliminar Tipo de Cliente (Admin)',
            'description' => 'Puede eliminar un tipo de cliente (admin)',
            'description_english' => 'Can delete a customer type (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customer_types[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.customer_types.destroy'], [
            'name' => 'Eliminar Tipo de Cliente (Quality)',
            'description' => 'Puede eliminar un tipo de cliente (quality)',
            'description_english' => 'Can delete a customer type (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customer_types[] = $perm->id;
        // Asignación de permisos a roles
        $rol_admin->permissions()->syncWithoutDetaching($permisos_admin_customer_types);
        $rol_quality->permissions()->syncWithoutDetaching($permisos_quality_customer_types);

        // Permisos CRUD Servicios
        $permisos_admin_services = [];
        $permisos_quality_services = [];
        // index
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.services.index'], [
            'name' => 'Ver listado de Servicios (Admin)',
            'description' => 'Puede ver el listado de servicios (admin)',
            'description_english' => 'Can view services list (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_services[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.services.index'], [
            'name' => 'Ver listado de Servicios (Quality)',
            'description' => 'Puede ver el listado de servicios (quality)',
            'description_english' => 'Can view services list (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_services[] = $perm->id;
        // create
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.services.create'], [
            'name' => 'Crear Servicio (Admin)',
            'description' => 'Puede acceder al formulario de creación de servicios (admin)',
            'description_english' => 'Can access service creation form (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_services[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.services.create'], [
            'name' => 'Crear Servicio (Quality)',
            'description' => 'Puede acceder al formulario de creación de servicios (quality)',
            'description_english' => 'Can access service creation form (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_services[] = $perm->id;
        // store
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.services.store'], [
            'name' => 'Registrar Servicio (Admin)',
            'description' => 'Puede registrar un nuevo servicio (admin)',
            'description_english' => 'Can store a new service (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_services[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.services.store'], [
            'name' => 'Registrar Servicio (Quality)',
            'description' => 'Puede registrar un nuevo servicio (quality)',
            'description_english' => 'Can store a new service (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_services[] = $perm->id;
        // edit
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.services.edit'], [
            'name' => 'Editar Servicio (Admin)',
            'description' => 'Puede acceder al formulario de edición de servicios (admin)',
            'description_english' => 'Can access service edit form (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_services[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.services.edit'], [
            'name' => 'Editar Servicio (Quality)',
            'description' => 'Puede acceder al formulario de edición de servicios (quality)',
            'description_english' => 'Can access service edit form (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_services[] = $perm->id;
        // update
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.services.update'], [
            'name' => 'Actualizar Servicio (Admin)',
            'description' => 'Puede actualizar un servicio (admin)',
            'description_english' => 'Can update a service (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_services[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.services.update'], [
            'name' => 'Actualizar Servicio (Quality)',
            'description' => 'Puede actualizar un servicio (quality)',
            'description_english' => 'Can update a service (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_services[] = $perm->id;
        // destroy
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.services.destroy'], [
            'name' => 'Eliminar Servicio (Admin)',
            'description' => 'Puede eliminar un servicio (admin)',
            'description_english' => 'Can delete a service (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_services[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.services.destroy'], [
            'name' => 'Eliminar Servicio (Quality)',
            'description' => 'Puede eliminar un servicio (quality)',
            'description_english' => 'Can delete a service (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_services[] = $perm->id;
        // Asignación de permisos a roles
        $rol_admin->permissions()->syncWithoutDetaching($permisos_admin_services);
        $rol_quality->permissions()->syncWithoutDetaching($permisos_quality_services);

        // Permisos para Service Packages
        $permisos_admin_service_packages = [];
        $permisos_quality_service_packages = [];

        // index
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.service_packages.index'], [
            'name' => 'Ver listado de Paquetes de Servicio (Admin)',
            'description' => 'Puede ver el listado de paquetes de servicio (admin)',
            'description_english' => 'Can view service packages list (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_service_packages[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.service_packages.index'], [
            'name' => 'Ver listado de Paquetes de Servicio (Quality)',
            'description' => 'Puede ver el listado de paquetes de servicio (quality)',
            'description_english' => 'Can view service packages list (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_service_packages[] = $perm->id;

        // create
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.service_packages.create'], [
            'name' => 'Crear Paquete de Servicio (Admin)',
            'description' => 'Puede acceder al formulario de creación de paquetes de servicio (admin)',
            'description_english' => 'Can access service package creation form (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_service_packages[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.service_packages.create'], [
            'name' => 'Crear Paquete de Servicio (Quality)',
            'description' => 'Puede acceder al formulario de creación de paquetes de servicio (quality)',
            'description_english' => 'Can access service package creation form (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_service_packages[] = $perm->id;

        // store
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.service_packages.store'], [
            'name' => 'Registrar Paquete de Servicio (Admin)',
            'description' => 'Puede registrar un nuevo paquete de servicio (admin)',
            'description_english' => 'Can store a new service package (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_service_packages[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.service_packages.store'], [
            'name' => 'Registrar Paquete de Servicio (Quality)',
            'description' => 'Puede registrar un nuevo paquete de servicio (quality)',
            'description_english' => 'Can store a new service package (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_service_packages[] = $perm->id;

        // edit
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.service_packages.edit'], [
            'name' => 'Editar Paquete de Servicio (Admin)',
            'description' => 'Puede acceder al formulario de edición de paquetes de servicio (admin)',
            'description_english' => 'Can access service package edit form (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_service_packages[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.service_packages.edit'], [
            'name' => 'Editar Paquete de Servicio (Quality)',
            'description' => 'Puede acceder al formulario de edición de paquetes de servicio (quality)',
            'description_english' => 'Can access service package edit form (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_service_packages[] = $perm->id;

        // update
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.service_packages.update'], [
            'name' => 'Actualizar Paquete de Servicio (Admin)',
            'description' => 'Puede actualizar un paquete de servicio (admin)',
            'description_english' => 'Can update a service package (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_service_packages[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.service_packages.update'], [
            'name' => 'Actualizar Paquete de Servicio (Quality)',
            'description' => 'Puede actualizar un paquete de servicio (quality)',
            'description_english' => 'Can update a service package (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_service_packages[] = $perm->id;

        // destroy
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.service_packages.destroy'], [
            'name' => 'Eliminar Paquete de Servicio (Admin)',
            'description' => 'Puede eliminar un paquete de servicio (admin)',
            'description_english' => 'Can delete a service package (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_service_packages[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.service_packages.destroy'], [
            'name' => 'Eliminar Paquete de Servicio (Quality)',
            'description' => 'Puede eliminar un paquete de servicio (quality)',
            'description_english' => 'Can delete a service package (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_service_packages[] = $perm->id;

        // Permisos para Customers
        $permisos_admin_customers = [];
        $permisos_quality_customers = [];

        // index
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customers.index'], [
            'name' => 'Ver listado de Clientes (Admin)',
            'description' => 'Puede ver el listado de clientes (admin)',
            'description_english' => 'Can view customers list (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customers[] = $perm->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.quality.customers.index'], [
            'name' => 'Ver listado de Clientes (Quality)',
            'description' => 'Puede ver el listado de clientes (quality)',
            'description_english' => 'Can view customers list (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customers[] = $permision->id;

        $rol_quality = Role::where('slug', 'lscefa.quality')->first();
        $rol_quality->permissions()->syncWithoutDetaching($permisos_quality_customers);

        // create
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customers.create'], [
            'name' => 'Crear Cliente (Admin)',
            'description' => 'Puede acceder al formulario de creación de clientes (admin)',
            'description_english' => 'Can access customer creation form (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customers[] = $perm->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.quality.customers.create'], [
            'name' => 'Crear Cliente (Quality)',
            'description' => 'Puede acceder al formulario de creación de clientes (quality)',
            'description_english' => 'Can access customer creation form (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customers[] = $permision->id;
        $rol_quality = Role::where('slug', 'lscefa.quality')->first();
        $rol_quality->permissions()->syncWithoutDetaching($permisos_quality_customers);

        // store
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customers.store'], [
            'name' => 'Registrar Cliente (Admin)',
            'description' => 'Puede registrar un nuevo cliente (admin)',
            'description_english' => 'Can store a new customer (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customers[] = $perm->id;

        // Permiso para registrar un cliente (quality)

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.quality.customers.store'], [
            'name' => 'Registrar Cliente (Quality)',
            'description' => 'Puede registrar un nuevo cliente (quality)',
            'description_english' => 'Can store a new customer (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customers[] = $permision->id;
        $rol_quality = Role::where('slug', 'lscefa.quality')->first();
        $rol_quality->permissions()->syncWithoutDetaching($permisos_quality_customers);

        // edit
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customers.edit'], [
            'name' => 'Editar Cliente (Admin)',
            'description' => 'Puede acceder al formulario de edición de clientes (admin)',
            'description_english' => 'Can access customer edit form (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customers[] = $perm->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.quality.customers.edit'], [
            'name' => 'Editar Cliente (Quality)',
            'description' => 'Puede acceder al formulario de edición de clientes (quality)',
            'description_english' => 'Can access customer edit form (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customers[] = $permision->id;
        $rol_quality = Role::where('slug', 'lscefa.quality')->first();
        $rol_quality->permissions()->syncWithoutDetaching($permisos_quality_customers);

        // update
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customers.update'], [
            'name' => 'Actualizar Cliente (Admin)',
            'description' => 'Puede actualizar un cliente (admin)',
            'description_english' => 'Can update a customer (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customers[] = $perm->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.quality.customers.update'], [
            'name' => 'Actualizar Cliente (Quality)',
            'description' => 'Puede actualizar un cliente (quality)',
            'description_english' => 'Can update a customer (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customers[] = $permision->id;
        $rol_quality = Role::where('slug', 'lscefa.quality')->first();
        $rol_quality->permissions()->syncWithoutDetaching($permisos_quality_customers);

        // destroy
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.customers.destroy'], [
            'name' => 'Eliminar Cliente (Admin)',
            'description' => 'Puede eliminar un cliente (admin)',
            'description_english' => 'Can delete a customer (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_customers[] = $perm->id;

        $permision = Permission::updateOrCreate(['slug' => 'lscefa.quality.customers.destroy'], [
            'name' => 'Eliminar Cliente (Quality)',
            'description' => 'Puede eliminar un cliente (quality)',
            'description_english' => 'Can delete a customer (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_customers[] = $permision->id;
        $rol_quality = Role::where('slug', 'lscefa.quality')->first();
        $rol_quality->permissions()->syncWithoutDetaching($permisos_quality_customers);

        // Permisos CRUD Cotizaciones
        $permisos_admin_quotes = [];
        $permisos_quality_quotes = [];
        // index
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.quotes.index'], [
            'name' => 'Ver listado de Cotizaciones (Admin)',
            'description' => 'Puede ver el listado de cotizaciones (admin)',
            'description_english' => 'Can view quotes list (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.quotes.index'], [
            'name' => 'Ver listado de Cotizaciones (Quality)',
            'description' => 'Puede ver el listado de cotizaciones (quality)',
            'description_english' => 'Can view quotes list (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        // create
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.quotes.create'], [
            'name' => 'Crear Cotización (Admin)',
            'description' => 'Puede acceder al formulario de creación de cotizaciones (admin)',
            'description_english' => 'Can access quote creation form (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.quotes.create'], [
            'name' => 'Crear Cotización (Quality)',
            'description' => 'Puede acceder al formulario de creación de cotizaciones (quality)',
            'description_english' => 'Can access quote creation form (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        // store
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.quotes.store'], [
            'name' => 'Registrar Cotización (Admin)',
            'description' => 'Puede registrar una nueva cotización (admin)',
            'description_english' => 'Can store a new quote (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.quotes.store'], [
            'name' => 'Registrar Cotización (Quality)',
            'description' => 'Puede registrar una nueva cotización (quality)',
            'description_english' => 'Can store a new quote (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        // edit
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.quotes.edit'], [
            'name' => 'Editar Cotización (Admin)',
            'description' => 'Puede acceder al formulario de edición de cotizaciones (admin)',
            'description_english' => 'Can access quote edit form (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.quotes.edit'], [
            'name' => 'Editar Cotización (Quality)',
            'description' => 'Puede acceder al formulario de edición de cotizaciones (quality)',
            'description_english' => 'Can access quote edit form (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        // update
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.quotes.update'], [
            'name' => 'Actualizar Cotización (Admin)',
            'description' => 'Puede actualizar una cotización (admin)',
            'description_english' => 'Can update a quote (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.quotes.update'], [
            'name' => 'Actualizar Cotización (Quality)',
            'description' => 'Puede actualizar una cotización (quality)',
            'description_english' => 'Can update a quote (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        // destroy
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.quotes.destroy'], [
            'name' => 'Eliminar Cotización (Admin)',
            'description' => 'Puede eliminar una cotización (admin)',
            'description_english' => 'Can delete a quote (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.quotes.destroy'], [
            'name' => 'Eliminar Cotización (Quality)',
            'description' => 'Puede eliminar una cotización (quality)',
            'description_english' => 'Can delete a quote (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        // show
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.quotes.show'], [
            'name' => 'Ver detalle de Cotización (Admin)',
            'description' => 'Puede ver el detalle de una cotización (admin)',
            'description_english' => 'Can view quote details (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.quotes.show'], [
            'name' => 'Ver detalle de Cotización (Quality)',
            'description' => 'Puede ver el detalle de una cotización (quality)',
            'description_english' => 'Can view quote details (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        // pdf
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.quotes.pdf'], [
            'name' => 'Descargar PDF de Cotización (Admin)',
            'description' => 'Puede descargar el PDF de una cotización (admin)',
            'description_english' => 'Can download quote PDF (admin)',
            'app_id' => $app->id
        ]);
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.quotes.pdf'], [
            'name' => 'Descargar PDF de Cotización (Quality)',
            'description' => 'Puede descargar el PDF de una cotización (quality)',
            'description_english' => 'Can download quote PDF (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        // Permiso para ver y subir comprobante de cotización
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.quotes.upload'], [
            'name' => 'Ver y subir comprobante de Cotización (Quality)',
            'description' => 'Puede ver el formulario y subir comprobantes de cotización (quality)',
            'description_english' => 'Can view the form and upload quote files (quality)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        $permisos_admin_quotes[] = $perm->id;

        // Permiso para iniciar procesos por terreno
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.process.start'], [
            'name' => 'Iniciar procesos por terreno',
            'description' => 'Puede iniciar y gestionar procesos asociados a cotizaciones por terreno',
            'description_english' => 'Can start and manage processes associated with quotes by unit/land',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        $permisos_admin_quotes[] = $perm->id;
        // Permiso para ver el listado global de procesos iniciados
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.processes.index'], [
            'name' => 'Ver listado de procesos iniciados (Quality)',
            'description' => 'Puede ver el listado global de procesos iniciados',
            'description_english' => 'Can view the global list of started processes',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.processes.index'], [
            'name' => 'Ver listado de procesos iniciados (Admin)',
            'description' => 'Puede ver el listado global de procesos iniciados (admin)',
            'description_english' => 'Can view the global list of started processes (admin)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        $permisos_admin_quotes[] = $perm->id;
        // Permiso para ver el detalle de un proceso individual
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.processes.show'], [
            'name' => 'Ver detalle de proceso (Quality)',
            'description' => 'Puede ver el detalle individual de un proceso',
            'description_english' => 'Can view the detail of a process',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.processes.show'], [
            'name' => 'Ver detalle de proceso (Admin)',
            'description' => 'Puede ver el detalle individual de un proceso (admin)',
            'description_english' => 'Can view the detail of a process (admin)',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        $permisos_admin_quotes[] = $perm->id;
        // Permiso para eliminar un proceso
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.processes.destroy'], [
            'name' => 'Eliminar proceso (Quality)',
            'description' => 'Puede eliminar procesos desde la gestión de calidad',
            'description_english' => 'Can delete processes from quality management',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        $permisos_admin_quotes[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.admin.processes.destroy'], [
            'name' => 'Eliminar proceso (Admin)',
            'description' => 'Puede eliminar procesos desde la gestión de admin',
            'description_english' => 'Can delete processes from admin management',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        $permisos_admin_quotes[] = $perm->id;
        // Asignación de permisos a roles
        $rol_admin->permissions()->syncWithoutDetaching($permisos_admin_quotes);
        $rol_quality->permissions()->syncWithoutDetaching($permisos_quality_quotes);

        // Permisos para descarga de archivos
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.quality.download.files'], [
            'name' => 'Descargar archivos',
            'description' => 'Permite descargar archivos del sistema (comprobantes y comunicaciones)',
            'description_english' => 'Allows downloading files from the system (receipts and communications)',
            'app_id' => $app->id
        ]);
        $permissions_quality[] = $permission->id;
        $permissions_admin[] = $permission->id; // Los administradores también pueden descargar archivos

        // Asignar permisos a los roles
        $rol_admin->permissions()->syncWithoutDetaching($permissions_admin);
        $rol_quality->permissions()->syncWithoutDetaching($permissions_quality);

        // Permiso para descargar archivos de comunicación
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.communication_file.download'], [
            'name' => 'Descargar archivo de comunicación (Quality)',
            'description' => 'Puede descargar archivos de comunicación desde la gestión de calidad',
            'description_english' => 'Can download communication files from quality management',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        $permisos_admin_quotes[] = $perm->id;

        // Permiso para descargar comprobantes
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.quality.comprobante_file.download'], [
            'name' => 'Descargar comprobante de cotización (Quality)',
            'description' => 'Puede descargar comprobantes de cotización desde la gestión de calidad',
            'description_english' => 'Can download quote receipts from quality management',
            'app_id' => $app->id
        ]);
        $permisos_quality_quotes[] = $perm->id;
        $permisos_admin_quotes[] = $perm->id;

        // Permisos para análisis de pH (Personal Técnico)
        $permisos_technical_ph = [];

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.ph_analysis.index'], [
            'name' => 'Ver gestión de análisis de pH',
            'description' => 'Puede ver la gestión de análisis de pH',
            'description_english' => 'Can view pH analysis management',
            'app_id' => $app->id
        ]);

        $permisos_technical_ph[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.ph_analysis.process_all'], [
            'name' => 'Procesar todos los análisis de pH',
            'description' => 'Puede procesar todos los análisis de pH pendientes',
            'description_english' => 'Can process all pending pH analyses',
            'app_id' => $app->id
        ]);
        $permisos_technical_ph[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.ph_analysis.store'], [
            'name' => 'Guardar análisis de pH',
            'description' => 'Puede guardar análisis de pH',
            'description_english' => 'Can store pH analysis',
            'app_id' => $app->id
        ]);
        $permisos_technical_ph[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.ph_analysis.ph_analysis'], [
            'name' => 'Ver formulario de análisis de pH',
            'description' => 'Puede ver el formulario de análisis de pH',
            'description_english' => 'Can view pH analysis form',
            'app_id' => $app->id
        ]);
        $permisos_technical_ph[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.ph_analysis.store_ph_analysis'], [
            'name' => 'Guardar análisis de pH específico',
            'description' => 'Puede guardar un análisis de pH específico',
            'description_english' => 'Can store a specific pH analysis',
            'app_id' => $app->id
        ]);
        $permisos_technical_ph[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.ph_analysis.download_report'], [
            'name' => 'Descargar reporte de pH',
            'description' => 'Puede descargar reportes de análisis de pH',
            'description_english' => 'Can download pH analysis reports',
            'app_id' => $app->id
        ]);
        $permisos_technical_ph[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.ph_analysis.batch_ph_analysis'], [
            'name' => 'Procesar lote de análisis de pH',
            'description' => 'Puede procesar lotes de análisis de pH',
            'description_english' => 'Can process batches of pH analyses',
            'app_id' => $app->id
        ]);
        $permisos_technical_ph[] = $perm->id;

        // Permisos alineados con los nombres de ruta actuales para re-procesar análisis devueltos (pH)
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.ph_analysis.show'], [
            'name' => 'Ver formulario de análisis de pH (ruta show)',
            'description' => 'Puede abrir el formulario del análisis de pH para re-procesar',
            'description_english' => 'Can open pH analysis form (show) to reprocess',
            'app_id' => $app->id
        ]);
        $permisos_technical_ph[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.ph_analysis.download'], [
            'name' => 'Descargar reporte de pH (ruta download)',
            'description' => 'Puede descargar el reporte del análisis de pH',
            'description_english' => 'Can download pH analysis report',
            'app_id' => $app->id
        ]);
        $permisos_technical_ph[] = $perm->id;

        // Asignar permisos de pH al rol técnico (refuerzo)
        $rol_technical->permissions()->syncWithoutDetaching($permisos_technical_ph);

        // Permisos para análisis de conductividad (Personal Técnico)
        $permisos_technical_conductivity = [];

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.conductivity_analysis.index'], [
            'name' => 'Ver gestión de análisis de conductividad',
            'description' => 'Puede ver la gestión de análisis de conductividad',
            'description_english' => 'Can view conductivity analysis management',
            'app_id' => $app->id
        ]);

        $permisos_technical_conductivity[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.conductivity_analysis.batch_conductivity_analysis'], [
            'name' => 'Procesar lote de análisis de conductividad',
            'description' => 'Permite seleccionar y procesar lotes de análisis de conductividad',
            'description_english' => 'Can process batches of conductivity analyses',
            'app_id' => $app->id
        ]);
        $permisos_technical_conductivity[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.conductivity_analysis.process_all'], [
            'name' => 'Ver formulario de procesamiento de lote de conductividad',
            'description' => 'Permite acceder al formulario de procesamiento de lotes de análisis de conductividad',
            'description_english' => 'Can access the form for processing conductivity batches',
            'app_id' => $app->id
        ]);
        $permisos_technical_conductivity[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.conductivity_analysis.show'], [
            'name' => 'Procesar análisis de conductividad individual',
            'description' => 'Permite procesar un análisis de conductividad individualmente',
            'description_english' => 'Can process a specific conductivity analysis',
            'app_id' => $app->id
        ]);
        $permisos_technical_conductivity[] = $perm->id;

        $perm = Permission::updateOrCreate(['slug' => 'lscefa.conductivity_analysis.store'], [
            'name' => 'Guardar análisis de conductividad',
            'description' => 'Permite guardar análisis de conductividad',
            'description_english' => 'Can store conductivity analysis',
            'app_id' => $app->id
        ]);
        $permisos_technical_conductivity[] = $perm->id;

        // Asignar permisos de conductividad al rol técnico
        $rol_technical->permissions()->syncWithoutDetaching($permisos_technical_conductivity);

        // Permisos para análisis de micronutrientes (Personal Técnico)
        $permisos_technical_micronutrients = [];
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.micronutrients.index'], [
            'name' => 'Ver gestión de análisis de micronutrientes',
            'description' => 'Puede ver la gestión de análisis de micronutrientes',
            'description_english' => 'Can view micronutrients analysis management',
            'app_id' => $app->id
        ]);
        $permisos_technical_micronutrients[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.micronutrients.store'], [
            'name' => 'Guardar análisis de micronutrientes',
            'description' => 'Puede guardar análisis de micronutrientes',
            'description_english' => 'Can store micronutrients analysis',
            'app_id' => $app->id
        ]);
        $permisos_technical_micronutrients[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.micronutrients.batch'], [
            'name' => 'Procesar lote de análisis de micronutrientes',
            'description' => 'Puede procesar lotes de análisis de micronutrientes',
            'description_english' => 'Can process batches of micronutrients analyses',
            'app_id' => $app->id
        ]);
        $permisos_technical_micronutrients[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.micronutrients.batch_store'], [
            'name' => 'Guardar lote de análisis de micronutrientes',
            'description' => 'Puede guardar lotes de análisis de micronutrientes',
            'description_english' => 'Can store batches of micronutrients analyses',
            'app_id' => $app->id
        ]);
        $permisos_technical_micronutrients[] = $perm->id;
        $perm = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.micronutrients.show'], [
            'name' => 'Ver detalle de análisis de micronutrientes',
            'description' => 'Puede ver el detalle de análisis de micronutrientes',
            'description_english' => 'Can view micronutrients analysis detail',
            'app_id' => $app->id
        ]);
        $permisos_technical_micronutrients[] = $perm->id;
        $rol_technical->permissions()->syncWithoutDetaching($permisos_technical_micronutrients);

        // Asignar todos los permisos al rol de administrador al final del método run()
        $adminRole = Role::where('slug', 'lscefa.admin')->first();
        $allPermissions = Permission::where('app_id', $app->id)->pluck('id')->toArray();
        $adminRole->permissions()->syncWithoutDetaching($allPermissions);

        // Permiso para ver el listado de análisis de textura (Técnico y Calidad y Admin)
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.texture.index'], [
            'name' => 'Ver listado de Análisis de Textura',
            'description' => 'Puede ver el listado de análisis de textura',
            'description_english' => 'Can view the list of texture analyses',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;
        // Permisos adicionales de textura SOLO para técnico
        $texture_permissions = [
            ['slug' => 'lscefa.technical.analyses.texture.store', 'name' => 'Guardar análisis de textura', 'description' => 'Puede guardar análisis de textura', 'description_english' => 'Can store texture analysis'],
            ['slug' => 'lscefa.technical.analyses.texture.batch', 'name' => 'Procesar lote de análisis de textura', 'description' => 'Puede procesar lotes de análisis de textura', 'description_english' => 'Can process batches of texture analyses'],
            ['slug' => 'lscefa.technical.analyses.texture.batch.post', 'name' => 'Procesar lote de análisis de textura (POST)', 'description' => 'Puede procesar lotes de análisis de textura (POST)', 'description_english' => 'Can process batches of texture analyses (POST)'],
            ['slug' => 'lscefa.technical.analyses.texture.batch_store', 'name' => 'Guardar lote de análisis de textura', 'description' => 'Puede guardar lotes de análisis de textura', 'description_english' => 'Can store batches of texture analyses'],
            ['slug' => 'lscefa.technical.analyses.texture.show', 'name' => 'Ver detalle de análisis de textura', 'description' => 'Puede ver el detalle de análisis de textura', 'description_english' => 'Can view texture analysis detail'],
            ['slug' => 'lscefa.technical.analyses.texture.edit', 'name' => 'Editar análisis de textura', 'description' => 'Puede editar análisis de textura', 'description_english' => 'Can edit texture analysis'],
            ['slug' => 'lscefa.technical.analyses.texture.update', 'name' => 'Actualizar análisis de textura', 'description' => 'Puede actualizar análisis de textura', 'description_english' => 'Can update texture analysis'],
            ['slug' => 'lscefa.technical.analyses.texture.destroy', 'name' => 'Eliminar análisis de textura', 'description' => 'Puede eliminar análisis de textura', 'description_english' => 'Can delete texture analysis'],
            ['slug' => 'lscefa.technical.analyses.texture.report', 'name' => 'Descargar reporte de textura', 'description' => 'Puede descargar reportes de análisis de textura', 'description_english' => 'Can download texture analysis reports'],
            ['slug' => 'lscefa.technical.analyses.texture.edit_rejected', 'name' => 'Editar análisis de textura rechazado', 'description' => 'Puede editar análisis de textura que han sido rechazados', 'description_english' => 'Can edit rejected texture analysis'],
            ['slug' => 'lscefa.technical.analyses.texture.update_rejected', 'name' => 'Actualizar análisis de textura rechazado', 'description' => 'Puede actualizar análisis de textura que han sido rechazados', 'description_english' => 'Can update rejected texture analysis'],
        ];
        foreach ($texture_permissions as $tp) {
            $perm = Permission::updateOrCreate(['slug' => $tp['slug']], [
                'name' => $tp['name'],
                'description' => $tp['description'],
                'description_english' => $tp['description_english'],
                'app_id' => $app->id
            ]);
            $permissions_technical[] = $perm->id;
        }

        // Permiso para ver el listado de análisis de boro (Técnico y Calidad y Admin)
        $permision = Permission::updateOrCreate(['slug' => 'lscefa.technical.analyses.boron.index'], [
            'name' => 'Ver listado de Análisis de Boro',
            'description' => 'Puede ver el listado de análisis de boro',
            'description_english' => 'Can view the list of boron analyses',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permision->id;
        
        // Permisos adicionales de boro SOLO para técnico
        $boron_permissions = [
            ['slug' => 'lscefa.technical.analyses.boron.process', 'name' => 'Procesar análisis de boro individual', 'description' => 'Puede procesar análisis de boro individuales', 'description_english' => 'Can process individual boron analysis'],
            ['slug' => 'lscefa.technical.analyses.boron.store', 'name' => 'Guardar análisis de boro', 'description' => 'Puede guardar análisis de boro', 'description_english' => 'Can store boron analysis'],
            ['slug' => 'lscefa.technical.analyses.boron.batch', 'name' => 'Procesar lote de análisis de boro', 'description' => 'Puede procesar lotes de análisis de boro', 'description_english' => 'Can process batches of boron analyses'],
            ['slug' => 'lscefa.technical.analyses.boron.batch.post', 'name' => 'Procesar lote de análisis de boro (POST)', 'description' => 'Puede procesar lotes de análisis de boro (POST)', 'description_english' => 'Can process batches of boron analyses (POST)'],
            ['slug' => 'lscefa.technical.analyses.boron.batch_store', 'name' => 'Guardar lote de análisis de boro', 'description' => 'Puede guardar lotes de análisis de boro', 'description_english' => 'Can store batches of boron analyses'],
            ['slug' => 'lscefa.technical.analyses.boron.show', 'name' => 'Ver detalle de análisis de boro', 'description' => 'Puede ver el detalle de análisis de boro', 'description_english' => 'Can view boron analysis detail'],
            ['slug' => 'lscefa.technical.analyses.boron.edit', 'name' => 'Editar análisis de boro', 'description' => 'Puede editar análisis de boro', 'description_english' => 'Can edit boron analysis'],
            ['slug' => 'lscefa.technical.analyses.boron.update', 'name' => 'Actualizar análisis de boro', 'description' => 'Puede actualizar análisis de boro', 'description_english' => 'Can update boron analysis'],
            ['slug' => 'lscefa.technical.analyses.boron.destroy', 'name' => 'Eliminar análisis de boro', 'description' => 'Puede eliminar análisis de boro', 'description_english' => 'Can delete boron analysis'],
            ['slug' => 'lscefa.technical.analyses.boron.report', 'name' => 'Descargar reporte de boro', 'description' => 'Puede descargar reportes de análisis de boro', 'description_english' => 'Can download boron analysis reports'],
            ['slug' => 'lscefa.technical.analyses.boron.edit_rejected', 'name' => 'Editar análisis de boro rechazado', 'description' => 'Puede editar análisis de boro que han sido rechazados', 'description_english' => 'Can edit rejected boron analysis'],
            ['slug' => 'lscefa.technical.analyses.boron.update_rejected', 'name' => 'Actualizar análisis de boro rechazado', 'description' => 'Puede actualizar análisis de boro que han sido rechazados', 'description_english' => 'Can update rejected boron analysis'],
        ];
        foreach ($boron_permissions as $bp) {
            $perm = Permission::updateOrCreate(['slug' => $bp['slug']], [
                'name' => $bp['name'],
                'description' => $bp['description'],
                'description_english' => $bp['description_english'],
                'app_id' => $app->id
            ]);
            $permissions_technical[] = $perm->id;
        }

        // Asignar permisos de textura y boro al rol técnico
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);
    }
}