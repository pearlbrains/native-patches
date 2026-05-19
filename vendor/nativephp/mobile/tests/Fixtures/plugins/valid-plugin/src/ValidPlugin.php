<?php

namespace Test\ValidPlugin;

class ValidPlugin
{
    public function execute(string $param1): mixed
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('ValidPlugin.Execute', json_encode([
                'param1' => $param1,
            ]));

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data ?? null;
            }
        }

        return null;
    }

    public function getStatus(): ?object
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('ValidPlugin.GetStatus', '{}');

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data ?? null;
            }
        }

        return null;
    }
}
