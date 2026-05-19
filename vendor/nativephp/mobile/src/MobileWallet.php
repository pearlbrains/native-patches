<?php

namespace Native\Mobile;

class MobileWallet
{
    /**
     * Check if Apple Pay or Google Pay is available on the device
     */
    public function isAvailable(): bool
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileWallet.IsAvailable', '{}');

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data->available ?? false;
            }
        }

        return false;
    }

    /**
     * Create a Stripe payment intent for the transaction
     *
     * @param  int  $amount  Amount in smallest currency unit (cents for USD)
     * @param  string  $currency  Three-letter ISO currency code (lowercase)
     * @param  array  $metadata  Additional metadata to attach to the payment
     * @return object|null Payment intent data including client_secret
     */
    public function createPaymentIntent(int $amount, string $currency = 'usd', array $metadata = []): ?object
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileWallet.CreatePaymentIntent', json_encode([
                'amount' => $amount,
                'currency' => strtolower($currency),
                'metadata' => $metadata,
            ]));

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data ?? null;
            }
        }

        return null;
    }

    /**
     * Present the native payment sheet for card selection
     *
     * @param  string  $clientSecret  The client secret from the payment intent
     * @param  string  $merchantDisplayName  The merchant name to display on the payment sheet
     * @param  string  $publishableKey  The Stripe publishable key
     * @param  string  $merchantId  The Apple Pay merchant ID (e.g., "merchant.com.yourapp")
     * @param  string  $merchantCountryCode  ISO country code (default: "US")
     * @param  array  $options  Additional options for the payment sheet
     * @return object|null Result of presenting the payment sheet
     */
    public function presentPaymentSheet(
        string $clientSecret,
        string $merchantDisplayName,
        string $publishableKey,
        string $merchantId,
        string $merchantCountryCode = 'US',
        array $options = []
    ): ?object {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileWallet.PresentPaymentSheet', json_encode([
                'clientSecret' => $clientSecret,
                'merchantDisplayName' => $merchantDisplayName,
                'publishableKey' => $publishableKey,
                'merchantId' => $merchantId,
                'merchantCountryCode' => $merchantCountryCode,
                'options' => $options,
            ]));

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data ?? null;
            }
        }

        return null;
    }

    /**
     * Confirm the payment with the selected payment method
     *
     * @param  string  $paymentIntentId  The payment intent ID to confirm
     * @return object|null Confirmation result
     */
    public function confirmPayment(string $paymentIntentId): ?object
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileWallet.ConfirmPayment', json_encode([
                'paymentIntentId' => $paymentIntentId,
            ]));

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data ?? null;
            }
        }

        return null;
    }

    /**
     * Get the current payment status
     *
     * @param  string  $paymentIntentId  The payment intent ID to check
     * @return object|null Payment status information
     */
    public function getPaymentStatus(string $paymentIntentId): ?object
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('MobileWallet.GetPaymentStatus', json_encode([
                'paymentIntentId' => $paymentIntentId,
            ]));

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data ?? null;
            }
        }

        return null;
    }
}
