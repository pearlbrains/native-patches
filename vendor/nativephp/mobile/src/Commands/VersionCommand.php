<?php

namespace Native\Mobile\Commands;

use Composer\InstalledVersions;
use Illuminate\Console\Command;

class VersionCommand extends Command
{
    protected $signature = 'native:version';

    protected $description = 'Display the current NativePHP Mobile package version';

    public function handle(): void
    {
        try {
            // Use getPrettyVersion for human-readable output (e.g., "dev-v3" instead of "3.9999999.9999999.9999999-dev")
            $version = InstalledVersions::getPrettyVersion('nativephp/mobile');
            $this->info("📱 NativePHP Mobile: {$version}");
        } catch (\Exception $e) {
            // Fallback to reading composer.json if InstalledVersions fails
            $composerPath = __DIR__.'/../../composer.json';
            if (file_exists($composerPath)) {
                $composer = json_decode(file_get_contents($composerPath), true);
                $version = $composer['version'] ?? 'Unknown';
                $this->info("📱 NativePHP Mobile: {$version}");
            } else {
                $this->error('Unable to determine package version');
            }
        }
    }
}
