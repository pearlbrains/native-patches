<?php

return [
    /**
     * An internal flag to indicate if the app is running in the NativePHP
     * environment. This is used to determine if the app should use the
     * NativePHP database and storage paths.
     */
    'running' => env('NATIVEPHP_RUNNING', false),

    'platform' => env('NATIVEPHP_PLATFORM'),

    /**
     * The path to the temporary directory for the NativePHP app.
     * On iOS, this is the system temporary directory.
     * On Android, this is the app's cache directory.
     */
    'tempdir' => env('NATIVEPHP_TEMPDIR'),
];
