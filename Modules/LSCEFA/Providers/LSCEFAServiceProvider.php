<?php

namespace Modules\LSCEFA\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Modules\LSCEFA\Http\Middleware\CheckLSCEFARole;
use Modules\LSCEFA\Http\Middleware\RedirectIfNotLSCEFA;
use Modules\LSCEFA\Http\Middleware\CheckLSCEFAPermission;

class LSCEFAServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'LSCEFA';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'lscefa';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        // Registrar los middlewares
        $this->app['router']->aliasMiddleware('lscefa.role', CheckLSCEFARole::class);
        $this->app['router']->aliasMiddleware('lscefa.redirect', RedirectIfNotLSCEFA::class);
        $this->app['router']->aliasMiddleware('lscefa.permission', CheckLSCEFAPermission::class);
        
        // Registrar comandos del módulo
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\LSCEFA\Console\Commands\CheckAnalyticalControls::class,
                \Modules\LSCEFA\Console\Commands\FixAnalyticalControls::class,
                \Modules\LSCEFA\Console\Commands\FixSpecificControl::class,
                \Modules\LSCEFA\Console\Commands\TestHumidityAnalysis::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
        
        // Agregar el namespace del módulo
        $this->loadViewsFrom($sourcePath, 'lscefa');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
