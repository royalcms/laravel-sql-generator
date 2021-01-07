<?php

namespace Royalcms\Component\LaravelSqlGenerator;

use Royalcms\Component\LaravelSqlGenerator\Console\SqlCommand;
use Illuminate\Support\ServiceProvider;

class SqlGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->publish();

        $this->publishes([
            __DIR__.'/sql_generator.php' => config_path("sql_generator.php"),
        ]);
    }

    /**
     * Publish config file.
     */
    protected function publish()
    {
        $source = realpath($raw = __DIR__.'/config/sql_generator.php') ?: $raw;

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $source => config_path('sql_generator.php'),
            ]);
        }

        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom($source, 'sql_generator');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();
    }

    /**
     * Register all of sql generator command.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands('command.generate.sql');

        $this->registerInstallCommand();
    }

    /**
     * @return void
     */
    protected function registerInstallCommand()
    {
        $this->app->singleton('command.generate.sql', function($app) {
            return new SqlCommand($app['migrator']);
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            'command.generate.sql'
        ];
    }
}
