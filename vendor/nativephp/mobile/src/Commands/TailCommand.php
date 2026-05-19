<?php

namespace Native\Mobile\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class TailCommand extends Command
{
    protected $signature = 'native:tail';

    protected $description = 'Tail Laravel logs from the Android app';

    public function handle(): void
    {
        $appId = config('nativephp.app_id');

        if (empty($appId)) {
            $this->error('🚫 NATIVEPHP_APP_ID is not set');
            $this->line('Please add a NATIVEPHP_APP_ID to your .env file (e.g. com.example.myapp).');

            return;
        }

        $this->tailAndroid($appId);
    }

    private function tailAndroid(string $appId): void
    {
        $this->info("🤖 Tailing Android logs for app: $appId");
        $this->line("Press Ctrl+C to stop...\n");

        $command = [
            'adb', 'shell', 'run-as', $appId, 'tail', '-f',
            'app_storage/persisted_data/storage/logs/laravel.log',
        ];

        $process = new Process($command);
        $process->setTimeout(null);

        try {
            $process->start();

            foreach ($process as $type => $data) {
                if ($process::OUT === $type) {
                    $this->line($data, null, null, false);
                } else {
                    $this->error($data, null, null, false);
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Error running tail command: {$e->getMessage()}");
            $this->line('Make sure:');
            $this->line('• ADB is installed and in your PATH');
            $this->line('• An Android device/emulator is connected');
            $this->line('• The app is installed and running');
        }
    }
}
