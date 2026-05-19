<?php

namespace Test\ValidPlugin;

use Illuminate\Support\ServiceProvider;

class ValidPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ValidPlugin::class, function ($app) {
            return new ValidPlugin;
        });
    }

    public function boot(): void
    {
        // Boot logic here
    }
}
