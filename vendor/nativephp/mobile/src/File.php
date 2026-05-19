<?php

namespace Native\Mobile;

class File
{
    public function move(string $from, string $to): bool
    {
        if (function_exists('nativephp_call')) {
            $params = [
                'from' => $from,
                'to' => $to,
            ];

            $result = nativephp_call('File.Move', json_encode($params));

            // Decode the result if it's JSON
            if (is_string($result)) {
                $decoded = json_decode($result, true);
                if (isset($decoded['success'])) {
                    return $decoded['success'];
                }
                // If there's an error key, it failed
                if (isset($decoded['error'])) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function copy(string $from, string $to): bool
    {
        if (function_exists('nativephp_call')) {
            $params = [
                'from' => $from,
                'to' => $to,
            ];

            $result = nativephp_call('File.Copy', json_encode($params));

            // Decode the result if it's JSON
            if (is_string($result)) {
                $decoded = json_decode($result, true);
                if (isset($decoded['success'])) {
                    return $decoded['success'];
                }
                // If there's an error key, it failed
                if (isset($decoded['error'])) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
