<?php

namespace Native\Mobile;

class Network
{
    /**
     * Get the current network status.
     * Returns an object with:
     * - connected: bool - Whether device is connected to network
     * - type: string - Connection type (wifi, cellular, ethernet, unknown)
     * - isExpensive: bool - Whether connection is metered/cellular (iOS only)
     * - isConstrained: bool - Whether Low Data Mode is enabled (iOS only)
     */
    public function status(): ?object
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('Network.Status', '{}');

            if ($result) {
                $decoded = json_decode($result);

                return $decoded ?? null;
            }
        }

        return null;
    }
}
