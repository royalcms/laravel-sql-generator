<?php

namespace Royalcms\Laravel\SqlGenerator\Console;

use Illuminate\Database\Migrations\Migrator;
use Royalcms\Laravel\SqlGenerator\SqlFormatter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SqlCommand extends Command
{
    protected $signature        = 'sql:generate 
                {--path=* : The path(s) to the migrations files to be executed}
                {--output=* : The path to the raw SQL scripts output path}';

    protected $description = 'convert Laravel migrations to raw SQL scripts';

    /**
     * The migrator instance.
     *
     * @var \Illuminate\Database\Migrations\Migrator
     */
    protected $migrator;

    public function __construct(Migrator $migrator)
    {
        parent::__construct();

        // Create object of migration
        $this->migrator = $migrator;
    }

    public function handle()
    {
        $path = $this->option('path') ?: base_path() . '/database/migrations';
        $output = $this->option('output') ?: Config::get('sql_generator.defaultDirectory');

        // Now that we have the connections we can resolve it and pretend to run the
        // queries against the database returning the array of raw SQL statements
        // that would get fired against the database system for this migration.
        $db = $this->migrator->resolveConnection(null);
        $files = $migrations = $this->migrator->getMigrationFiles($path);
        $this->migrator->requireFiles($files);
        //
        $sql = "-- convert Laravel migrations to raw SQL scripts --\n";
        foreach ($migrations as $migration) {
            // First we will resolve a "real" instance of the migration class from this
            // migration file name. Once we have the instances we can run the actual
            // command such as "up" or "down", or we can just simulate the action.
            $migration_name = $this->migrator->getMigrationName($migration);
            $migration      = $this->migrator->resolve($migration_name);
            $name           = "";

            $querys = $db->pretend(function () use ($migration) {
                $migration->up();
            });

            foreach ($querys as $query) {
                if ($name != $migration_name) {
                    $name = $migration_name;
                    $sql  .= "\n-- migration:" . $name . " --\n";
                }
                if (substr($query['query'], 0, 11) === "insert into") {
                    $sql .= "-- insert data -- \n";
                    foreach ($query['bindings'] as $item) {
                        $query['query'] = str_replace_first("?", "'" . $item . "'", $query['query']);
                    }
                }
                $query['query'] = SqlFormatter::format($query['query'], false);
                $sql            .= $query['query'] . ";\n";
            }
        }

        $dir = $output;
        //Check directory exit or not
        if (is_dir($dir) === false) {
            // Make directory in database folder
            mkdir($dir);
        }

        // Pull query in sql file
        File::put($dir . '/database.sql', $sql);

        $this->comment("Sql script create successfully");
    }
}
