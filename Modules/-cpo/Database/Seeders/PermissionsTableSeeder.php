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

        $permissions_admin = []; // Almacenar permisos para rol
        // Consultar aplicación SICA para registrar los roles
        $app = App::where('name', 'LSCEFA')->first();


        // Permisos Rol (Administrador)
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.index'], [ // Registro o actualización de permiso
            'name' => 'Vista de configuración (Administrador)',
            'description' => 'Configuración de parametros generales y testeo de impresión pos',
            'description_english' => 'Configuration of general parameters and post printing test',
            'app_id' => $app->id
        ]);

        $permission = Permission::updateOrCreate(['slug' => 'lscefa.admin.welcome'], [ // Registro o actualización de permiso
            'name' => 'Vista de configuración (Administrador)',
            'description' => 'Configuración de parametros generales y testeo de impresión pos',
            'description_english' => 'Configuration of general parameters and post printing test',
            'app_id' => $app->id
        ]);

        $permissions_admin[] = $permission->id;
        $rol_admin = Role::where('slug', 'lscefa.admin')->first(); // Rol Administrador
        $rol_admin->permissions()->syncWithoutDetaching($permissions_admin);





        // Permisos Rol (Pasante)

        $permissions_intern = [];
        $app = App::where('name', 'LSCEFA')->first();

        $permission = Permission::updateOrCreate(['slug' => 'LSCEFA.intern.panelpas'], [ // Registro o actualización de permiso
            'name' => 'Vista de configuración (Pasante)',
            'description' => 'Configuración de parametros generales y testeo de impresión pos',
            'description_english' => 'Configuration of general parameters and post printing test',
            'app_id' => $app->id
        ]);
        
        $permissions_intern[] = $permission->id;
        $rol_intern = Role::where('slug', 'lscefa.intern')->first(); // Rol Pasante
        $rol_intern->permissions()->syncWithoutDetaching($permissions_intern);




         // Permisos Rol (Personal Tecnico)
         
        $permissions_technical = [];
        $app = App::where('name', 'LSCEFA')->first(); 
        $permission = Permission::updateOrCreate(['slug' => 'lscefa.technical.panelpas'], [ // Registro o actualización de permiso
            'name' => 'Vista de configuración (Personal Tecnico)',
            'description' => 'recolección, preparación y análisis de muestras',
            'description_english' => 'Collection, preparation and analysis of samples',
            'app_id' => $app->id
        ]);
        $permissions_technical[] = $permission->id;
        $rol_technical = Role::where('slug', 'lscefa.technical')->first(); // Rol Pasante
        $rol_technical->permissions()->syncWithoutDetaching($permissions_technical);
    }
}