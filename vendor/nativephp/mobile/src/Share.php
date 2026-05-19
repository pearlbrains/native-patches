<?php

namespace Native\Mobile;

class Share
{
    public function url(string $title, string $text, string $url): void
    {
        if (function_exists('nativephp_call')) {
            $params = [
                'title' => $title,
                'text' => $text,
                'url' => $url,
            ];

            nativephp_call('Share.Url', json_encode($params));
        }
    }

    public function file(string $title, string $text, string $filePath): void
    {
        if (function_exists('nativephp_call')) {
            $params = [
                'title' => $title,
                'message' => $text,
                'filePath' => $filePath,
            ];

            nativephp_call('Share.File', json_encode($params));
        }
    }
}
