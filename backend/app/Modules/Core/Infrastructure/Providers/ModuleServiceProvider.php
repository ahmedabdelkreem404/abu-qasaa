<?php

namespace App\Modules\Core\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName;

    public function boot(): void
    {
        $migrationPath = app_path("Modules/{$this->moduleName}/Infrastructure/Migrations");

        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }
}
