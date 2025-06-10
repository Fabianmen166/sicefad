<?php

namespace Modules\LSCEFA\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\SICA\Entities\App;
use Modules\SICA\Entities\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $app = App::where('name', 'LSCEFA')->firstOrFail();

        $roleadmin = Role::updateOrCreate(['slug' => 'LSCEFA.admin'], [
            'name' => 'Administrador',
            'description' => 'Rol administrador del Modulo LSCEFA',
            'description_english' => 'Administrator role of the LSCEFA Module',
            'full_access' => 'No',
            'app_id' => $app->id,
        ]);

        $useradministrador = User::where('nickname', 'Crmuñoz')->firstOrFail();
        $useradministrador->roles()->syncWithoutDetaching([$roleadmin->id]);

        $roleintern = Role::updateOrCreate(['slug' => 'LSCEFA.intern'], [
            'name' => 'Pasante',
            'description' => 'Rol pasante del modulo LSCEFA',
            'description_english' => 'Intern role of the LSCEFA Module',
            'full_access' => 'No',
            'app_id' => $app->id,
        ]);

        $userintern = User::where('nickname', 'Armon')->firstOrFail();
        $userintern->roles()->syncWithoutDetaching([$roleintern->id]);

        $roletechnical = Role::updateOrCreate(['slug' => 'LSCEFA.technical'], [
            'name' => 'Personal Tecnico',
            'description' => 'Rol Personal Tecnico del modulo LSCEFA',
            'description_english' => 'Technical Staff role of the LSCEFA Module',
            'full_access' => 'No',
            'app_id' => $app->id,
        ]);

        $usertechnical = User::where('nickname', 'Fabian')->firstOrFail();
        $usertechnical->roles()->syncWithoutDetaching([$roleintern->id]);

    

        
        }
    }