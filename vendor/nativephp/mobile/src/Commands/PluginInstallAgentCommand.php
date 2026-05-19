<?php

namespace Native\Mobile\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\outro;

class PluginInstallAgentCommand extends Command
{
    protected $signature = 'native:plugin:install-agent
                            {--force : Overwrite existing agent files}
                            {--all : Install all agents without prompting}';

    protected $description = 'Install AI agents for NativePHP plugin development';

    protected Filesystem $files;

    protected array $availableAgents = [
        'kotlin-android-expert' => 'Kotlin/Android Expert - Deep Android native development',
        'swift-ios-expert' => 'Swift/iOS Expert - Deep iOS native development',
        'js-bridge-expert' => 'JS/TS Bridge Expert - JavaScript client-side integration',
        'plugin-writer' => 'Plugin Writer - General plugin scaffolding and structure',
        'plugin-docs-writer' => 'Plugin Docs Writer - Documentation and Boost guidelines',
    ];

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        intro('Install NativePHP Plugin Development Agents');

        $sourcePath = dirname(__DIR__, 2).'/.claude/agents';
        $destDir = base_path('.claude/agents');

        // Check which agents are available
        $available = [];
        foreach ($this->availableAgents as $agent => $description) {
            if ($this->files->exists("{$sourcePath}/{$agent}.md")) {
                $available[$agent] = $description;
            }
        }

        if (empty($available)) {
            $this->error('No agent definitions found in the package.');

            return self::FAILURE;
        }

        // Determine which agents to install
        if ($this->option('all')) {
            $selected = array_keys($available);
        } else {
            $selected = multiselect(
                label: 'Which agents would you like to install?',
                options: $available,
                default: array_keys($available),
                hint: 'Space to toggle, Enter to confirm',
            );
        }

        if (empty($selected)) {
            $this->info('No agents selected.');

            return self::SUCCESS;
        }

        // Create destination directory
        $this->files->ensureDirectoryExists($destDir);

        // Install selected agents
        $installed = 0;
        $skipped = 0;

        $this->newLine();

        foreach ($selected as $agent) {
            $source = "{$sourcePath}/{$agent}.md";
            $dest = "{$destDir}/{$agent}.md";

            if ($this->files->exists($dest) && ! $this->option('force')) {
                $this->components->twoColumnDetail($agent, '<fg=yellow>skipped (exists)</>');
                $skipped++;

                continue;
            }

            $this->files->copy($source, $dest);
            $this->components->twoColumnDetail($agent, '<fg=green>installed</>');
            $installed++;
        }

        $this->newLine();

        if ($installed > 0) {
            outro("Installed {$installed} agent(s)".($skipped > 0 ? ", skipped {$skipped}" : ''));
        } else {
            $this->info('All selected agents already exist. Use --force to overwrite.');
        }

        $this->newLine();
        $this->components->twoColumnDetail('<fg=yellow>Usage</>', '');
        $this->newLine();
        $this->components->bulletList([
            '<info>kotlin-android-expert</info> - Implements Android Kotlin bridge functions',
            '<info>swift-ios-expert</info> - Implements iOS Swift bridge functions',
            '<info>js-bridge-expert</info> - Implements JavaScript/TypeScript client code',
            '<info>plugin-writer</info> - Creates plugin structure and manifest',
            '<info>plugin-docs-writer</info> - Creates documentation and Boost guidelines',
        ]);

        return self::SUCCESS;
    }
}
