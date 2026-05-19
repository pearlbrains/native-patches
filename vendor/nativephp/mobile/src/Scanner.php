<?php

namespace Native\Mobile;

class Scanner
{
    /**
     * Create a new QR code scanner instance.
     */
    public static function scan(): PendingScanner
    {
        return new PendingScanner;
    }

    /**
     * Alias for scan() to match other NativePHP patterns.
     */
    public static function make(): PendingScanner
    {
        return static::scan();
    }
}
