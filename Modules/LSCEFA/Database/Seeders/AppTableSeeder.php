<?php

namespace Modules\LSCEFA\Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\SICA\Entities\App;

class AppTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

     

      
        $App = App::create([
            'name' => 'LSCEFA',
            'url' => '/lscefa/index',
            'color' => '#FF5733',
            'icon' => 'fas fa-globe',
            'description' => 'Sistema de Control Para la Informacion de los procesos que se realizan en el Laboratorio',
            'description_english' => 'System for controlling the information of the processes carried out in the Laboratory',
             
        ]);
         
    }
};
