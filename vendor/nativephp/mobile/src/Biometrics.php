<?php

namespace Native\Mobile;

class Biometrics
{
    /**
     * Initiate a biometric authentication prompt.
     * Returns a PendingBiometric instance for fluent API usage.
     */
    public function prompt(): PendingBiometric
    {
        return new PendingBiometric;
    }
}
