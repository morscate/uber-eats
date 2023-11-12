<?php

namespace Morscate\UberEats;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class UberEatsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        if ($this->app instanceof Application && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/uber-eats.php' => config_path('uber-eats.php'),
            ], 'uber-eats-config');
        }
    }

    /**
     * Setup the configuration.
     */
    protected function configure(): void
    {
        $source = realpath($raw = __DIR__.'/../config/uber-eats.php') ?: $raw;

        $this->mergeConfigFrom($source, 'uber-eats');
    }
}
