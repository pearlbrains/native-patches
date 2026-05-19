<?php

define('ARTISAN_START', microtime(true));

if (! isset($argv)) {
    global $argv;
    $argv = $_SERVER['argv'] ?? ['artisan'];
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\StreamOutput;

// ✅ Redirect output to php://output so ub_write captures it
$stdout = fopen('php://output', 'w');
$output = new StreamOutput($stdout);

$kernel = $app->make(Kernel::class);

$status = $kernel->handle(
    new ArgvInput,
    $output
);

$kernel->terminate(new ArgvInput, $status);

exit($status);
