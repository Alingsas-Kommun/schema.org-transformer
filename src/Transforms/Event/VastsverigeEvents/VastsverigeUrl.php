<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents;

/**
 * Normalizes absolute and relative URLs from the Västsverige API.
 */
final class VastsverigeUrl
{
    /**
     * Turn a source URL into an absolute URL.
     *
     * @param string|null $url     Source URL, relative path, or host without a scheme.
     * @param string      $baseUrl Origin used for paths that start with a slash.
     *
     * @return string|null Absolute URL, or null when the source value is empty.
     */
    public static function absolute(?string $url, string $baseUrl): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return rtrim($baseUrl, '/') . $url;
        }

        return 'https://' . $url;
    }
}
