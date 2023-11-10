<?php

namespace Morscate\UberEats;

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
        //
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
