<?php

namespace Native\Mobile\Events\Wallet;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $paymentIntentId,
        public ?string $reason = null
    ) {}
}
