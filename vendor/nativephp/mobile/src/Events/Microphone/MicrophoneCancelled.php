<?php

namespace Native\Mobile\Events\Microphone;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MicrophoneCancelled
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public bool $cancelled = true,
        public ?string $id = null
    ) {}
}
