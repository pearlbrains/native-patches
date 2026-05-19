<?php

namespace Native\Mobile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isAvailable()
 * @method static object|null createPaymentIntent(int $amount, string $currency = 'usd', array $metadata = [])
 * @method static object|null presentPaymentSheet(string $clientSecret, string $merchantDisplayName, string $publishableKey, string $merchantId, string $merchantCountryCode = 'US', array $options = [])
 * @method static object|null confirmPayment(string $paymentIntentId)
 * @method static object|null getPaymentStatus(string $paymentIntentId)
 *
 * @see \Native\Mobile\MobileWallet
 */
class MobileWallet extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Native\Mobile\MobileWallet::class;
    }
}
