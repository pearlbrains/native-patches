<?php

namespace Test\NativeCodePlugin;

class NativeCodePlugin
{
    public function execute(string $param1): mixed
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('NativeCode.Execute', json_encode([
                'param1' => $param1,
            ]));

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data ?? null;
            }
        }

        return null;
    }

    public function getData(): ?object
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('NativeCode.GetData', '{}');

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data ?? null;
            }
        }

        return null;
    }
}
