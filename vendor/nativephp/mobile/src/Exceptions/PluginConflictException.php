<?php

namespace Native\Mobile\Exceptions;

use RuntimeException;

class PluginConflictException extends RuntimeException
{
    protected array $conflicts;

    public function __construct(array $conflicts)
    {
        $this->conflicts = $conflicts;

        $messages = [];
        foreach ($conflicts as $conflict) {
            $plugins = implode(' and ', $conflict['plugins']);
            if ($conflict['type'] === 'namespace') {
                $messages[] = "Namespace '{$conflict['value']}' is used by both {$plugins}";
            } else {
                $messages[] = "Bridge function '{$conflict['value']}' is registered by both {$plugins}";
            }
        }

        parent::__construct(
            "Plugin conflicts detected:\n".implode("\n", $messages).
            "\n\nUnregister one of the conflicting plugins with: php artisan native:plugin:register <plugin> --remove"
        );
    }

    public function getConflicts(): array
    {
        return $this->conflicts;
    }
}
