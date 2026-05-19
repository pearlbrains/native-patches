<?php

namespace Native\Mobile;

use InvalidArgumentException;

class Browser
{
    /**
     * Open a URL in the system's default browser
     *
     * @param  string  $url  The URL to open
     * @return bool True if successfully opened
     */
    public function open(string $url): bool
    {
        if (function_exists('nativephp_call')) {
            $payload = json_encode([
                'url' => $url,
            ]);

            $result = nativephp_call('Browser.Open', $payload);

            if ($result) {
                $decoded = json_decode($result, true);

                return isset($decoded['success']) && $decoded['success'] === true;
            }
        }

        return false;
    }

    /**
     * Open a URL in an in-app browser window (SFSafariViewController on iOS, Custom Tabs on Android)
     *
     * @param  string  $url  The URL to open
     * @return bool True if successfully opened
     */
    public function inApp(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid URL provided');
        }

        if (function_exists('nativephp_call')) {
            $payload = json_encode([
                'url' => $url,
            ]);

            $result = nativephp_call('Browser.OpenInApp', $payload);

            if ($result) {
                $decoded = json_decode($result, true);

                return isset($decoded['success']) && $decoded['success'] === true;
            }
        }

        return false;
    }

    /**
     * Open a URL in an authentication session (ASWebAuthenticationSession on iOS)
     * Automatically handles OAuth callbacks with the configured deeplink scheme
     *
     * @param  string  $url  The URL to open for authentication
     * @return bool True if successfully opened
     */
    public function auth(string $url): bool
    {
        if (function_exists('nativephp_call')) {
            $payload = json_encode([
                'url' => $url,
            ]);

            $result = nativephp_call('Browser.OpenAuth', $payload);

            if ($result) {
                $decoded = json_decode($result, true);

                return isset($decoded['success']) && $decoded['success'] === true;
            }
        }

        return false;
    }
}
