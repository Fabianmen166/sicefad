<?php

namespace Modules\LSCEFA\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\SICA\Entities\Person;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Usuario 1: Crmuñoz
        $person = Person::where('document_number', 1075792846)->first();
        if ($person) {
            User::updateOrCreate(['nickname' => 'Crmuñoz'], [
                'person_id' => $person->id,
                'email' => 'camilomunozvallejo27@gmail.com',
                'password' => Hash::make('Crmu2846')
            ]);
        } else {
            Log::error('No se encontró la persona con document_number 1075792846');
        }

        // Usuario 2: Arleymon
        $person = Person::where('document_number', 1076983963)->first();
        if ($person) {
            User::updateOrCreate(['nickname' => 'Arleymon'], [
                'person_id' => $person->id,
                'email' => 'arleymonroy20@gmail.com',
                'password' => Hash::make('Armo3963')
            ]);
        } else {
            Log::error('No se encontró la persona con document_number 1076983963');
        }

        // Usuario 3: Fabiamen
        $person = Person::where('document_number', 1079605056)->first();
        if ($person) {
            User::updateOrCreate(['nickname' => 'Fabiamen'], [
                'person_id' => $person->id,
                'email' => 'alejandromedinaf05@gmail.com',
                'password' => Hash::make('Faale5056')
            ]);
        } else {
            Log::error('No se encontró la persona con document_number 1079605056');
        }

        // Usuario 4: MariaG (Gestor de Calidad)
        $person = Person::where('document_number', 1079605057)->first();
        if ($person) {
            User::updateOrCreate(['nickname' => 'MariaG'], [
                'person_id' => $person->id,
                'email' => 'mariagonzalez@gmail.com',
                'password' => Hash::make('Mago5057')
            ]);
        } else {
            Log::error('No se encontró la persona con document_number 1079605057');
        }
    }
}
