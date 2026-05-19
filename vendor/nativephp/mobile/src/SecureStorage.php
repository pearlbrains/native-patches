<?php

namespace Native\Mobile;

class SecureStorage
{
    /**
     * Store a secure value in the native keychain or keystore.
     *
     * @param  string  $key  The key to store the value under
     * @param  string|null  $value  The value to store securely
     * @return bool True if successfully stored, false otherwise
     */
    public function set(string $key, ?string $value): bool
    {
        if (function_exists('nativephp_call')) {
            $payload = json_encode([
                'key' => $key,
                'value' => $value,
            ]);

            $result = nativephp_call('SecureStorage.Set', $payload);

            if ($result) {
                $decoded = json_decode($result, true);

                return isset($decoded['success']) && $decoded['success'] === true;
            }
        }

        return false;
    }

    /**
     * Retrieve a secure value from the native keychain or keystore.
     *
     * @param  string  $key  The key to retrieve the value for
     * @return string|null The stored value or null if not found
     */
    public function get(string $key): ?string
    {
        if (function_exists('nativephp_call')) {
            $payload = json_encode([
                'key' => $key,
            ]);

            $result = nativephp_call('SecureStorage.Get', $payload);

            if ($result) {
                $decoded = json_decode($result, true);
                $value = $decoded['value'] ?? null;

                // Treat empty string as null (not found)
                return ($value === '' || $value === null) ? null : $value;
            }
        }

        return null;
    }

    /**
     * Delete a secure value from the native keychain or keystore.
     *
     * @param  string  $key  The key to delete the value for
     * @return bool True if successfully deleted, false otherwise
     */
    public function delete(string $key): bool
    {
        if (function_exists('nativephp_call')) {
            $payload = json_encode([
                'key' => $key,
            ]);

            $result = nativephp_call('SecureStorage.Delete', $payload);

            if ($result) {
                $decoded = json_decode($result, true);

                return isset($decoded['success']) && $decoded['success'] === true;
            }
        }

        return false;
    }
}
