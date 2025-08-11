<?php

namespace Modules\LSCEFA\Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\SICA\Entities\EPS;
use Modules\SICA\Entities\PensionEntity;
use Modules\SICA\Entities\Person;
use Modules\SICA\Entities\PopulationGroup;


class PeopleTableSeeder extends Seeder{
    public function run()
    {
        $population_group = PopulationGroup::firstOrCreate(['name'=>'NINGUNA']);
        $eps = EPS::firstOrCreate(['name'=>'NO REGISTRA']);
        $pension_entity = PensionEntity::firstOrCreate(['name'=>'NO REGISTRA']);

        Person::firstOrCreate(['document_number' => '1075792846' ],
        [
            'document_type' => 'Cedula Ciudadania',
            'first_name' => 'CRISTIAN',
            'first_last_name' => 'MUÑOZ',
            'second_last_name' => 'VALLEJO',
            'eps_id' => $eps->id,
            'population_group_id' => $population_group->id,
            'Pension_entity_id' => $pension_entity->id,
        ]);


        

    }      
   


    


}
