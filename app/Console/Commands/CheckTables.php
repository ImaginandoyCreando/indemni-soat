<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckTables extends Command
{
    protected $signature = 'db:check-tables {--filter= : Mostrar solo tablas que contengan este texto}';
    protected $description = 'Lista las tablas de la base de datos (uso diagnóstico, sólo CLI)';

    public function handle(): int
    {
        try {
            $driver = DB::connection()->getDriverName();
            $database = DB::connection()->getDatabaseName();

            $tableNames = match ($driver) {
                'mysql', 'mariadb' => array_map(
                    fn($t) => array_values((array) $t)[0],
                    DB::select('SHOW TABLES')
                ),
                'pgsql' => array_column(
                    DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"),
                    'table_name'
                ),
                'sqlite' => array_column(
                    DB::select("SELECT name FROM sqlite_master WHERE type='table'"),
                    'name'
                ),
                default => [],
            };

            $filter = $this->option('filter');
            if ($filter) {
                $tableNames = array_values(array_filter(
                    $tableNames,
                    fn($t) => str_contains($t, $filter)
                ));
            }

            $this->info("Driver: {$driver}");
            $this->info("Database: {$database}");
            $this->info('Total tablas: ' . count($tableNames));

            foreach ($tableNames as $name) {
                $this->line(' - ' . $name);
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
