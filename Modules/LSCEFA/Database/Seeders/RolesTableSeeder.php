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

        $roleadmin = Role::updateOrCreate(['slug' => 'lscefa.admin'], [
            'name' => 'Administrador',
            'description' => 'Rol administrador del Modulo LSCEFA',
            'description_english' => 'Administrator role of the LSCEFA Module',
            'full_access' => 'No',
            'app_id' => $app->id,
        ]);
        $useradministrador = User::where('nickname', 'Crmuñoz')->first();
        if ($useradministrador) {
            $useradministrador->roles()->sync([$roleadmin->id]);
        }

        $roleintern = Role::updateOrCreate(['slug' => 'lscefa.intern'], [
            'name' => 'Pasante',
            'description' => 'Rol pasante del modulo LSCEFA',
            'description_english' => 'Intern role of the LSCEFA Module',
            'full_access' => 'No',
            'app_id' => $app->id,
        ]);
        $userintern = User::where('nickname', 'Arleymon')->first();
        if ($userintern) {
            $userintern->roles()->sync([$roleintern->id]);
        }

        $roletechnical = Role::updateOrCreate(['slug' => 'lscefa.technical'], [
            'name' => 'Personal Tecnico',
            'description' => 'Rol Personal Tecnico del modulo LSCEFA',
            'description_english' => 'Technical Staff role of the LSCEFA Module',
            'full_access' => 'No',
            'app_id' => $app->id,
        ]);
        $usertechnical = User::where('nickname', 'Fabiamen')->first();
        if ($usertechnical) {
            $usertechnical->roles()->sync([$roletechnical->id]);
        }

        $rolequality = Role::updateOrCreate(['slug' => 'lscefa.quality'], [
            'name' => 'Gestor de Calidad',
            'description' => 'Rol Gestor de Calidad del modulo LSCEFA',
            'description_english' => 'Quality Manager role of the LSCEFA Module',
            'full_access' => 'No',
            'app_id' => $app->id,
        ]);
        $userquality = User::where('nickname', 'MariaG')->first();
        if ($userquality) {
            $userquality->roles()->sync([$rolequality->id]);
        }
    }
}