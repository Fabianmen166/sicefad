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

        Person::firstOrCreate(['document_number' => 1075792846 ],
        [
            'document_type' => 'Cedula Ciudadania',
            'first_name' => 'CRISTIAN',
            'first_last_name' => 'MUÑOZ',
            'second_last_name' => 'VALLEJO',
            'eps_id' => $eps->id,                                          //password: Crmu2846
            'population_group_id' => $population_group->id,
            'Pension_entity_id' => $pension_entity->id,
        ]);

        Person::firstOrCreate(['document_number' => 1076983963 ],
        [
            'document_type' => 'Cedula Ciudadania',
            'first_name' => 'ARLEY',                                //password: Armo3963
            'first_last_name' => 'MONROY',
            'second_last_name' => 'ORTEGA',
            'eps_id' => $eps->id,
            'population_group_id' => $population_group->id,
            'Pension_entity_id' => $pension_entity->id,
        ]);

        Person::firstOrCreate(['document_number' => 1079605056 ],
        [
            'document_type' => 'Cedula Ciudadania',
            'first_name' => 'KEVIN',                                //password: Keal5056
            'first_last_name' => 'ALEJANDRO',
            'second_last_name' => 'MEDINA',
            'eps_id' => $eps->id,
            'population_group_id' => $population_group->id,
            'Pension_entity_id' => $pension_entity->id,
        ]);




        

    }      
   


    


}