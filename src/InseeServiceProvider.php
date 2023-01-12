<?php

namespace NSpehler\LaravelInsee;

use Illuminate\Support\ServiceProvider;

class InseeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/config/shotgun_scan.php';

        $this->publishes([$configPath => config_path('insee.php')], 'config');
        $this->mergeConfigFrom($configPath, 'insee');

        if ($this->app instanceof Laravel\Lumen\Application) {
            $this->app->configure('insee');
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('laravel-insee', function ($app) {
            $config = isset($app['config']['services']['insee']) ? $app['config']['services']['insee'] : null;
            if (is_null($config)) {
                $config = $app['config']['insee'] ?: $app['config']['insee::config'];
            }

            return new InseeClient($config['guzzle_client_timeout']);
        });

        $this->app->alias('laravel-insee', InseeClient::class);
    }

    public function provides()
    {
        return ['laravel-insee'];
    }
}
