<?php

namespace Native\Mobile\Events\Biometric;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Completed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public bool $success,
        public ?string $id = null
    ) {}
}
