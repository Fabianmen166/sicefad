<?php

namespace Modules\LSCEFA\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\SICA\Entities\Person;
use Modules\SICA\Entities\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Obtener el rol de administrador
        $adminRole = Role::where('slug', 'lscefa.admin')->first();
        
        if (!$adminRole) {
            Log::error('No se encontró el rol lscefa.admin');
            return;
        }

        // Usuario 1: Crmuñoz (Administrador)
        $person = Person::where('document_number', 1075792846)->first();
        if ($person) {
            $user = User::updateOrCreate(
                ['nickname' => 'Crmuñoz'],
                [
                    'person_id' => $person->id,
                    'email' => 'camilomunozvallejo27@gmail.com',
                    'password' => Hash::make('Crmu2846')
                ]
            );
            
            // Asignar rol de administrador
            if (!$user->roles->contains($adminRole->id)) {
                $user->roles()->attach($adminRole->id);
            }
        } else {
            Log::error('No se encontró la persona con document_number 1075792846');
        }

        // Usuario 2: Arleymon
        $person = Person::where('document_number', 1076983963)->first();
        if ($person) {
            User::updateOrCreate(
                ['nickname' => 'Arleymon'],
                [
                    'person_id' => $person->id,
                    'email' => 'arleymonroy20@gmail.com',
                    'password' => Hash::make('Armo3963')
                ]
            );
        } else {
            Log::error('No se encontró la persona con document_number 1076983963');
        }

        // Usuario 3: Fabiamen
        $person = Person::where('document_number', 1079605056)->first();
        if ($person) {
            User::updateOrCreate(
                ['nickname' => 'Fabiamen'],
                [
                    'person_id' => $person->id,
                    'email' => 'alejandromedinaf05@gmail.com',
                    'password' => Hash::make('Faale5056')
                ]
            );
        } else {
            Log::error('No se encontró la persona con document_number 1079605056');
        }

        // Usuario 4: MariaG (Gestor de Calidad)
        $person = Person::where('document_number', 1079605057)->first();
        if ($person) {
            User::updateOrCreate(
                ['nickname' => 'MariaG'],
                [
                    'person_id' => $person->id,
                    'email' => 'mariagonzalez@gmail.com',
                    'password' => Hash::make('Mago5057')
                ]
            );
        } else {
            Log::error('No se encontró la persona con document_number 1079605057');
        }
    }
}
