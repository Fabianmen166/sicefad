<?php

namespace Modules\LSCEFA\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\SICA\Entities\App;
use Modules\SICA\Entities\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        try {
            $app = App::where('name', 'LSCEFA')->firstOrFail();

            // Rol Administrador
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
                Log::info('Rol administrador asignado a Crmuñoz');
            } else {
                Log::error('No se encontró el usuario Crmuñoz');
            }

            // Rol Pasante
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
                Log::info('Rol pasante asignado a Arleymon');
            } else {
                Log::error('No se encontró el usuario Arleymon');
            }

            // Rol Personal Técnico
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
                Log::info('Rol técnico asignado a Fabiamen');
            } else {
                Log::error('No se encontró el usuario Fabiamen');
            }

            // Rol Gestión de Calidad
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
                Log::info('Rol calidad asignado a MariaG');
            } else {
                Log::error('No se encontró el usuario MariaG');
            }
        } catch (\Exception $e) {
            Log::error('Error en RolesTableSeeder: ' . $e->getMessage());
            throw $e;
        }
    }
}