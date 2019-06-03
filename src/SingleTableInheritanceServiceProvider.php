<?php

namespace MannikJ\Laravel\SingleTableInheritance;

use MannikJ\Laravel\SingleTableInheritance\Services\SingleTableInheritance;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;

class SingleTableInheritanceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-sti');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-sti');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('single-table-inheritance.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-sti'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-sti'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-sti'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'single-table-inheritance');

        // Register the main class to use with the facade
        $this->app->singleton('sti', function () {
            return new SingleTableInheritance;
        });

        Blueprint::macro('sti', function ($name = null) {
            $name = $name ?: config('single-table-inheritance.column_name', 'type');
            return $this->string($name);
        });
    }
}
