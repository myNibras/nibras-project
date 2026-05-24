<?php

namespace App\Helpers;

class YoutubeUrl
{
    public static function extractId(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $patterns = [
            '#(?:https?://)?(?:www\.|m\.)?youtube\.com/watch\?[^#]*?v=([A-Za-z0-9_-]{6,})#i',
            '#(?:https?://)?youtu\.be/([A-Za-z0-9_-]{6,})#i',
            '#(?:https?://)?(?:www\.)?youtube\.com/embed/([A-Za-z0-9_-]{6,})#i',
            '#(?:https?://)?(?:www\.)?youtube\.com/shorts/([A-Za-z0-9_-]{6,})#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public static function toEmbedUrl(?string $url): ?string
    {
        $id = self::extractId($url);
        return $id ? 'https://www.youtube.com/embed/' . $id : null;
    }
}
