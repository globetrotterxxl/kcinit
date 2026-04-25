<?php

namespace Globetrotterxxl\KcInit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'kcinit:install';
    protected $description = 'Install Auth Controller, update .env and config';

    public function handle()
    {
        $this->info('Installing KcInit...');

        // 1. Publish/Copy Controller
        $this->publishController();

        // 2. Add .env variables
        $this->updateEnv();

        // 3. Append to config/services.php
        $this->updateServicesConfig();

        $this->info('Installation complete!');
    }

    protected function publishController()
    {

        $path = app_path('Http/Controllers/Auth/KeycloakAuthController.php');
        $directory = dirname($path);
        File::ensureDirectoryExists($directory);
        if (!File::exists($path)) {
            File::copy(__DIR__.'/../Http/Controllers/Auth/KeycloakAuthController.php', $path);
            $this->line('  - AuthController created.');
        }
    }

    protected function updateEnv()
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        if (!str_contains($envContent, 'KEYCLOAK_CLIENT_ID')) {
            $vars = "\nKEYCLOAK_CLIENT_ID=webapp-client\n";
            $vars.= "KEYCLOAK_CLIENT_SECRET=webapp-client-secret-change-me\n";
            $vars.= "KEYCLOAK_REDIRECT_URI=http://localhost:8000/auth/keycloak/callback\n";
            $vars.= "KEYCLOAK_BASE_URL=https://keycloak.local\n";
            $vars.= "KEYCLOAK_REALM=my-realm\n";
            File::append($envPath, $vars);
            $this->line('  - .env variables added.');
        }
    }

    protected function updateServicesConfig()
    {
        $path = config_path('services.php');
        $content = File::get($path);

        if (!str_contains($content, "'keycloak'")) {
            $stub = "\n    'keycloak' => [\n";
            $stub.= "        'client_id' => env('KEYCLOAK_CLIENT_ID'),\n";
            $stub.= "        'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),\n";
            $stub.= "        'redirect' => env('KEYCLOAK_REDIRECT_URI'),\n";
            $stub.= "        'base_url' => env('KEYCLOAK_BASE_URL'),\n";
            $stub.= "        'realms' => env('KEYCLOAK_REALM'),\n";
            $stub.= "    ],\n";

            // Insert before the last closing bracket
            $newContent = preg_replace('/];\s*$/', $stub . '];', $content);
            File::put($path, $newContent);
            $this->line('  - Config services updated.');
        }
    }
}
