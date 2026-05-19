<?php

namespace Pearlbrains\NativePatches;

use Illuminate\Support\ServiceProvider;
use Pearlbrains\NativePatches\Commands\PreCompileCommand;

class NativePatchesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PreCompileCommand::class,
            ]);
        }
    }
}
