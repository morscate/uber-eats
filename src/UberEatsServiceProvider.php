<?php

namespace Morscate\UberEats;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Morscate\UberEats\Controllers\WebhookController;

class UberEatsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishConfig();
        $this->registerMacros();
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

    protected function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/uber-eats.php' => config_path('uber-eats.php'),
            ], 'uber-eats');
        }

        $this->mergeConfigFrom(__DIR__.'/../config/uber-eats.php', 'uber-eats');
    }

    protected function registerMacros(): void
    {
        Route::macro('uberEatsWebhooks', function (string $uri = 'uber-eats/webhooks') {
            return $this->post($uri, [WebhookController::class, 'handle'])->name('uber-eats.webhooks');
        });
    }
}
