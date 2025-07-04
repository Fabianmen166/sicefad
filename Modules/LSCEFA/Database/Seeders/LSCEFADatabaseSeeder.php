<?php

namespace Modules\LSCEFA\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LSCEFADatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            // Primero ejecutamos el seeder de la aplicación
            $this->call(AppTableSeeder::class);
            
            // Luego ejecutamos el seeder de personas
            $this->call(PeopleTableSeeder::class);
            
            // Después ejecutamos el seeder de usuarios
            $this->call(UsersTableSeeder::class);
            
            // Finalmente ejecutamos los seeders de roles y permisos
            $this->call(RolesTableSeeder::class);
            $this->call(PermissionsTableSeeder::class);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

