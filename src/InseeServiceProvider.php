<?php

namespace NSpehler\LaravelInsee;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use NSpehler\LaravelInsee\Console\Commands\IssueInseeAccessToken;
use NSpehler\LaravelInsee\Console\Commands\PruneInseeAccessToken;

class InseeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/insee.php';

        $this->publishes([$configPath => config_path('insee.php')], 'insee-config');
        $this->mergeConfigFrom($configPath, 'insee');

        if ($this->app instanceof Laravel\Lumen\Application) {
            $this->app->configure('insee');
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        AboutCommand::add('Insee', fn() => ['Version' => '2.0.0']);

        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            IssueInseeAccessToken::class,
            PruneInseeAccessToken::class,
        ]);
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
