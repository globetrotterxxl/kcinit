<?php

namespace Globetrotterxxl\KcInit\Providers;

use Illuminate\Support\ServiceProvider;
use Globetrotterxxl\KcInit\Console\InstallCommand;

class KcInitServiceProvider extends ServiceProvider
{
    public function boot()
    {

        // Allow the user to run: php artisan vendor:publish --tag=kcinit-migrations
        $this->publishes([
            __DIR__.'/../../database/migrations/create_kc_init_table.php.stub' =>
                database_path('migrations/' . date('Y_m_d_His', time()) . '_create_kc_init_table.php'),
        ], 'kcinit-migrations');

        // Load routes from the package
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
