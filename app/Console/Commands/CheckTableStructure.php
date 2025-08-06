<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckTableStructure extends Command
{
    protected $signature = 'check:table-structure {table}';
    protected $description = 'Check the structure of a database table';

    public function handle()
    {
        $table = $this->argument('table');
        
        if (!Schema::hasTable($table)) {
            $this->error("Table {$table} does not exist");
            return;
        }
        
        $columns = Schema::getColumnListing($table);
        
        $this->info("Table: {$table}");
        $this->info("Columns:");
        
        foreach ($columns as $column) {
            $type = DB::getSchemaBuilder()->getColumnType($table, $column);
            $this->line("- {$column} ({$type})");
        }
    }
} 