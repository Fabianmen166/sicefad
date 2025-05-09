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
<<<<<<< HEAD

        $roleadmin = Role::updateOrCreate(['slug' => 'LSCEFA.admin'], [
=======
//Rol Administrador
        $roleadmin = Role::updateOrCreate(['slug' => 'lscefa.admin'], [
>>>>>>> 0e4ae791 (aaaaaa)
            'name' => 'Administrador',
            'description' => 'Rol administrador del Modulo LSCEFA',
            'description_english' => 'Administrator role of the LSCEFA Module',
            'full_access' => 'No',
            'app_id' => $app->id,
        ]);

        $useradministrador = User::where('nickname', 'Crmuñoz')->firstOrFail();
        $useradministrador->roles()->syncWithoutDetaching([$roleadmin->id]);

<<<<<<< HEAD
        $roleintern = Role::updateOrCreate(['slug' => 'LSCEFA.intern'], [
=======
//Rol Pasantes
        $roleintern = Role::updateOrCreate(['slug' => 'lscefa.intern'], [
>>>>>>> 0e4ae791 (aaaaaa)
            'name' => 'Pasante',
            'description' => 'Rol pasante del modulo LSCEFA',
            'description_english' => 'Intern role of the LSCEFA Module',
            'full_access' => 'No',
            'app_id' => $app->id,
        ]);

<<<<<<< HEAD
        $userintern = User::where('nickname', 'Armon')->firstOrFail();
        $userintern->roles()->syncWithoutDetaching([$roleintern->id]);

        $roletechnical = Role::updateOrCreate(['slug' => 'LSCEFA.technical'], [
=======
        $userintern = User::where('nickname', 'Arleymon')->firstOrFail();
        $userintern->roles()->syncWithoutDetaching([$roleintern->id]);

        //Rol Personal Tecnico

        $roletechnical = Role::updateOrCreate(['slug' => 'lscefa.technical'], [
>>>>>>> 0e4ae791 (aaaaaa)
            'name' => 'Personal Tecnico',
            'description' => 'Rol Personal Tecnico del modulo LSCEFA',
            'description_english' => 'Technical Staff role of the LSCEFA Module',
            'full_access' => 'No',
            'app_id' => $app->id,
        ]);

<<<<<<< HEAD
        $usertechnical = User::where('nickname', 'Fabian')->firstOrFail();
        $usertechnical->roles()->syncWithoutDetaching([$roleintern->id]);
=======
        $usertechnical = User::where('nickname', 'Fabianmen')->firstOrFail();
        $usertechnical->roles()->syncWithoutDetaching([$roletechnical->id]);
>>>>>>> 0e4ae791 (aaaaaa)

    

        
        }
    }