<?php

namespace Modules\LSCEFA\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SICA\Entities\EPS;
use Modules\SICA\Entities\PensionEntity;
use Modules\SICA\Entities\Person;
use Modules\SICA\Entities\PopulationGroup;

class PeopleTableSeeder extends Seeder
{
    public function run()
    {
        // Obtener o crear las entidades relacionadas
        $population_group = PopulationGroup::firstOrCreate(['name' => 'NINGUNA']);
        $eps = EPS::firstOrCreate(['name' => 'NO REGISTRA']);
        $pension_entity = PensionEntity::firstOrCreate(['name' => 'NO REGISTRA']);

        // Primer registro
        Person::firstOrCreate(
            ['document_number' => 1075792846],
            [
                'document_type' => 'Cédula de Ciudadanía',
                'first_name' => 'CRISTIAN',
                'first_last_name' => 'MUÑOZ',
                'second_last_name' => 'VALLEJO',
                'eps_id' => $eps->id,
                'population_group_id' => $population_group->id,
                'pension_entity_id' => $pension_entity->id,
            ]
        );

        // Segundo registro
        Person::firstOrCreate(
            ['document_number' => 1076983963],
            [
                'document_type' => 'Cédula de Ciudadanía',
                'first_name' => 'ARLEY',
                'first_last_name' => 'MONROY',
                'second_last_name' => 'ORTEGA',
                'eps_id' => $eps->id,
                'population_group_id' => $population_group->id,
                'pension_entity_id' => $pension_entity->id,
            ]
        );

        // Tercer registro
        Person::firstOrCreate(
            ['document_number' => 1079605056],
            [
                'document_type' => 'Cédula de Ciudadanía',
                'first_name' => 'FABIAN',
                'first_last_name' => 'ALEJANDRO',
                'second_last_name' => 'MEDINA',
                'eps_id' => $eps->id,
                'population_group_id' => $population_group->id,
                'pension_entity_id' => $pension_entity->id,
            ]
        );

        // Cuarto registro
        Person::firstOrCreate(
            ['document_number' => 1079605057],
            [
                'document_type' => 'Cédula de Ciudadanía',
                'first_name' => 'MARIA',
                'first_last_name' => 'GONZALEZ',
                'second_last_name' => 'RODRIGUEZ',
                'eps_id' => $eps->id,
                'population_group_id' => $population_group->id,
                'pension_entity_id' => $pension_entity->id,
            ]
        );
    }
}
