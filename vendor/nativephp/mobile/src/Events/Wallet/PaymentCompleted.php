<?php

namespace Native\Mobile\Events\Wallet;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $paymentIntentId,
        public int $amount,
        public string $currency,
        public string $status,
        public ?array $metadata = null
    ) {}
}
