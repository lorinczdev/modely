<?php

namespace Lorinczdev\Modely;

use Illuminate\Support\ServiceProvider;
use Lorinczdev\Modely\Routing\Route;
use Lorinczdev\Modely\Routing\RouteResourceOptions;

class ModelyServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lorinczdev');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'lorinczdev');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/modely.php', 'modely');

        // Register the service the package provides.
        $this->app->singleton(Modely::class, fn($app) => new Modely());
        $this->app->singleton(Route::class, fn() => new Route());
        $this->app->singleton(RouteResourceOptions::class, fn() => new RouteResourceOptions());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['modely'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/modely.php' => config_path('modely.php'),
        ], 'modely.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/lorinczdev'),
        ], 'modely.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/lorinczdev'),
        ], 'modely.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/lorinczdev'),
        ], 'modely.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
