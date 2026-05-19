<?php

namespace Native\Mobile\Events\Geolocation;

use Illuminate\Foundation\Events\Dispatchable;

class LocationReceived
{
    use Dispatchable;

    public function __construct(
        public bool $success,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?float $accuracy = null,
        public ?int $timestamp = null,
        public ?string $provider = null,
        public ?string $error = null,
        public ?string $id = null
    ) {}
}
