<?php

namespace Native\Mobile\Plugins;

use Illuminate\Support\Collection;

class PluginSecretsValidator
{
    protected Collection $plugins;

    protected $output = null;

    public function __construct(Collection $plugins)
    {
        $this->plugins = $plugins;
    }

    /**
     * Set the output interface for logging
     */
    public function setOutput($output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Validate all plugin secrets are available in the environment
     *
     * @return array{valid: bool, missing: array<string, array{plugin: string, secret: string, description: string}>}
     */
    public function validate(): array
    {
        $missing = [];

        foreach ($this->plugins as $plugin) {
            $secrets = $plugin->getSecrets();

            foreach ($secrets as $key => $value) {
                // Handle both formats:
                // 1. Simple array: ["SECRET_KEY"] -> $key=0, $value="SECRET_KEY"
                // 2. Associative: {"SECRET_KEY": {description, required}} -> $key="SECRET_KEY", $value=array
                if (is_string($value)) {
                    $secretName = $value;
                    $config = [];
                } else {
                    $secretName = $key;
                    $config = $value;
                }

                // Skip if not required
                if (isset($config['required']) && $config['required'] === false) {
                    continue;
                }

                $envValue = env($secretName);

                if ($envValue === null || $envValue === '') {
                    $missing[] = [
                        'plugin' => $plugin->name,
                        'secret' => $secretName,
                        'description' => $config['description'] ?? 'Required by '.$plugin->name,
                    ];
                }
            }
        }

        return [
            'valid' => empty($missing),
            'missing' => $missing,
        ];
    }

    /**
     * Validate and throw exception if secrets are missing
     *
     * @throws \RuntimeException
     */
    public function validateOrFail(): void
    {
        $result = $this->validate();

        if (! $result['valid']) {
            $message = $this->formatMissingSecretsMessage($result['missing']);

            throw new \RuntimeException($message);
        }
    }

    /**
     * Format missing secrets into a readable message
     */
    protected function formatMissingSecretsMessage(array $missing): string
    {
        $lines = ["Missing required plugin secrets:\n"];

        $byPlugin = [];
        foreach ($missing as $item) {
            $byPlugin[$item['plugin']][] = $item;
        }

        foreach ($byPlugin as $pluginName => $secrets) {
            $lines[] = "  Plugin: {$pluginName}";

            foreach ($secrets as $secret) {
                $lines[] = "    - {$secret['secret']}";
                $lines[] = "      {$secret['description']}";
            }

            $lines[] = '';
        }

        $lines[] = 'Add these to your .env file and try again.';

        return implode("\n", $lines);
    }

    /**
     * Get all secrets required by all plugins
     *
     * @return array<string, array{plugin: string, description: string, required: bool}>
     */
    public function getAllSecrets(): array
    {
        $allSecrets = [];

        foreach ($this->plugins as $plugin) {
            $secrets = $plugin->getSecrets();

            foreach ($secrets as $key => $value) {
                // Handle both formats:
                // 1. Simple array: ["SECRET_KEY"] -> $key=0, $value="SECRET_KEY"
                // 2. Associative: {"SECRET_KEY": {description, required}} -> $key="SECRET_KEY", $value=array
                if (is_string($value)) {
                    $secretName = $value;
                    $config = [];
                } else {
                    $secretName = $key;
                    $config = $value;
                }

                $allSecrets[$secretName] = [
                    'plugin' => $plugin->name,
                    'description' => $config['description'] ?? 'Required by '.$plugin->name,
                    'required' => $config['required'] ?? true,
                ];
            }
        }

        return $allSecrets;
    }

    /**
     * Output info message
     */
    protected function info(string $message): void
    {
        if ($this->output && method_exists($this->output, 'info')) {
            $this->output->info($message);
        }
    }

    /**
     * Output warning message
     */
    protected function warn(string $message): void
    {
        if ($this->output && method_exists($this->output, 'warn')) {
            $this->output->warn($message);
        }
    }
}
