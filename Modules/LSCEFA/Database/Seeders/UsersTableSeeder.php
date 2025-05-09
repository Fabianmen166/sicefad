<?php

namespace Modules\LSCEFA\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\SICA\Entities\Person;

<<<<<<< HEAD
class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $person = Person::where('document_number',1075792846)->first();
        User::updateOrCreate(['nickname' => 'Crmuñoz'], [
            'person_id' => $person->id,
            'email' => 'camilomunozvallejo27@gmail.com' // 'Crmu2846
        ]);

        $person = Person::where('document_number',1076983963)->first();
        User::updateOrCreate(['nickname' => 'Armon'], [
            'person_id' => $person->id,
            'email' => 'arleymonroy20@gmail.com' // 'Armo3963
        ]);

        $person = Person::where('document_number',1079605056)->first(); 
        User::updateOrCreate(['nickname' => 'Fabian'], [
            'person_id' => $person->id,
            'email' => 'alejandromedinaf05@gmail.com' // ' Keal5056
        ]);
         
       
   }
=======

class UsersTableSeeder extends Seeder{
    
    public function run()
    {
        $person = Person::where('document_number', '1075792846')->first();
        User::updateOrCreate(['nickname' =>'Cmuñoz'], [
        'person_id' => $person->id,
        'email' => 'camilomunozvallejo27@gmail.com'          //password: Crmu2846
        ]);


        
    }

>>>>>>> 8dd0f1a5 (Vista Modulo LSCEFA)
}