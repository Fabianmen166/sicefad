<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\SICA\Entities\Role;

class UpdateTechnicalRoleSlug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:update-technical-slug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el slug del rol técnico a lscefa.technical (minúsculas)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = Role::whereRaw('LOWER(slug) = ?', ['lscefa.technical'])->first();
        if ($role) {
            $role->slug = 'lscefa.technical';
            $role->save();
            $this->info('Slug del rol técnico actualizado correctamente a lscefa.technical');
        } else {
            $this->warn('No se encontró ningún rol técnico con slug similar a lscefa.technical');
        }
    }
} 