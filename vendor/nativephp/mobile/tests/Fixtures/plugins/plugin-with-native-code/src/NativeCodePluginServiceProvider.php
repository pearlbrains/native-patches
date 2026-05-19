<?php

namespace Test\NativeCodePlugin;

use Illuminate\Support\ServiceProvider;

class NativeCodePluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NativeCodePlugin::class, function ($app) {
            return new NativeCodePlugin;
        });
    }

    public function boot(): void
    {
        // Boot logic
    }
}
