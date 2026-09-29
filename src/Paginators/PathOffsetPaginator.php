<?php

declare(strict_types=1);

namespace SchemaTransformer\Paginators;

use SchemaTransformer\Interfaces\AbstractPaginator;

/**
 * Advances APIs that paginate with offset and limit as the last two path segments.
 *
 * Example: /api/get/sv/event/2671/0/100 becomes /api/get/sv/event/2671/100/100.
 */
final class PathOffsetPaginator implements AbstractPaginator
{
    /**
     * Build the next page URL by adding the page size to the offset.
     *
     * @param string               $previous Current page URL.
     * @param array<string, mixed> $headers  Response headers. Unused.
     *
     * @return string|false Next page URL, or false when the path has no offset and limit.
     */
    public function getNext(string $previous, array $headers): string | false
    {
        $parts = parse_url($previous);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        $path = $parts['path'] ?? '';
        if (preg_match('#/(\d+)/(\d+)$#', $path, $matches) !== 1) {
            return false;
        }

        $limit = (int) $matches[2];
        if ($limit < 1) {
            return false;
        }

        $nextPath = preg_replace(
            '#/\d+/\d+$#',
            '/' . ((int) $matches[1] + $limit) . '/' . $limit,
            $path
        );

        return $this->rebuild($parts, (string) $nextPath);
    }

    /**
     * Rebuild a URL from parse_url parts and a replaced path.
     *
     * @param array<string, mixed> $parts Parsed URL parts.
     * @param string               $path  Replacement path.
     *
     * @return string
     */
    private function rebuild(array $parts, string $path): string
    {
        $url = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $url .= ':' . $parts['port'];
        }

        $url .= $path;
        if (!empty($parts['query'])) {
            $url .= '?' . $parts['query'];
        }

        return $url;
    }
}
