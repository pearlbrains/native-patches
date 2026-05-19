<?php

namespace Native\Mobile\Events\Geolocation;

use Illuminate\Foundation\Events\Dispatchable;

class PermissionRequestResult
{
    use Dispatchable;

    public function __construct(
        public readonly string $location,
        public readonly string $coarseLocation,
        public readonly string $fineLocation,
        public readonly ?string $error = null,
        public readonly ?string $id = null
    ) {}
}
