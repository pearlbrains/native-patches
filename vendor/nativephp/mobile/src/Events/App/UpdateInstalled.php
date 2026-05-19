<?php

namespace Native\Mobile\Events\App;

use Illuminate\Foundation\Events\Dispatchable;

class UpdateInstalled
{
    use Dispatchable;

    public function __construct(
        public readonly string $version,
        public readonly int $timestamp
    ) {}
}
