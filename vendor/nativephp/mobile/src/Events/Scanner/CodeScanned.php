<?php

namespace Native\Mobile\Events\Scanner;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * QR code or barcode scanned event
 *
 * This event is dispatched from native code (Kotlin/Swift) via JavaScript injection,
 * directly triggering Livewire listeners with #[OnNative(CodeScanned::class)]
 */
class CodeScanned
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $data,
        public string $format,
        public ?string $id = null
    ) {}
}
