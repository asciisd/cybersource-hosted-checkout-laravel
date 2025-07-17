<?php

namespace Asciisd\Cybersource;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CybersourceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/cybersource.php' => config_path('cybersource.php'),
        ], 'cybersource-config');

        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/vendor/asciisd/cybersource'),
        ], 'cybersource-assets');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cybersource');

        Blade::component('cybersource::components.checkout', 'cybersource-checkout');
        Blade::component('cybersource::components.checkout-vue', 'cybersource-checkout-vue');
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
