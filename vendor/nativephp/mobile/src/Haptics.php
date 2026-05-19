<?php

namespace Native\Mobile;

class Haptics
{
    /**
     * Trigger a haptic vibration.
     *
     * @deprecated Use \Native\Mobile\Facades\Device::vibrate() instead
     */
    #[\Deprecated(message: 'Use \Native\Mobile\Facades\Device::vibrate() instead', since: '2.0.0')]
    public function vibrate(): void
    {
        // Use the new god method pattern via Device class
        if (function_exists('nativephp_call')) {
            nativephp_call('Device.Vibrate', '{}');
        }
    }
}
