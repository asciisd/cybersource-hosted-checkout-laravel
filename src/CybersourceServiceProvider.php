<?php

namespace Asciisd\Cybersource;

use Illuminate\Support\ServiceProvider;

class CybersourceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cybersource.php' => config_path('cybersource.php'),
            ], 'config');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cybersource');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/cybersource'),
        ], 'views');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cybersource.php', 'cybersource'
        );

        $this->app->singleton('cybersource', function () {
            return new \Asciisd\Cybersource\Cybersource;
        });
    }
} 