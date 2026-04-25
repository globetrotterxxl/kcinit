<?php

namespace Globetrotterxxl\KcInit\Providers;

use Illuminate\Support\ServiceProvider;
use Globetrotterxxl\KcInit\Console\InstallCommand;

class KcInitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load routes from the package
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
