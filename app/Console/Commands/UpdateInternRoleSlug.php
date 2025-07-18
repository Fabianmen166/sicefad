<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\SICA\Entities\Role;

class UpdateInternRoleSlug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:update-intern-slug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el slug del rol de pasante a lscefa.intern (minúsculas)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = Role::whereRaw('LOWER(slug) = ?', ['lscefa.intern'])->first();
        if ($role) {
            $role->slug = 'lscefa.intern';
            $role->save();
            $this->info('Slug del rol de pasante actualizado correctamente a lscefa.intern');
        } else {
            $this->warn('No se encontró ningún rol de pasante con slug similar a lscefa.intern');
        }
    }
} 