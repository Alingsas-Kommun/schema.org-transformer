<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents;

/**
 * Formats contact address parts from a Västsverige event.
 */
final class VastsverigeContact
{
    /**
     * Join street, zip and city without repeating parts already present in the street line.
     *
     * @param array<string, mixed> $contact Source contact object.
     *
     * @return string|null Single address line, or null when every part is empty.
     */
    public static function address(array $contact): ?string
    {
        $street = trim((string) ($contact['Street'] ?? ''));
        $zip    = trim((string) ($contact['Zip'] ?? ''));
        $city   = trim((string) ($contact['City'] ?? ''));
        $parts  = array_values(array_filter(
            [
                $street,
                self::unlessAlreadyInStreet($street, $zip),
                self::unlessAlreadyInStreet($street, $city),
            ],
            fn (string $part) => $part !== ''
        ));

        if ($parts === []) {
            return null;
        }

        return implode(', ', $parts);
    }

    /**
     * Keep an address part only when the street line does not already contain it.
     *
     * @param string $street Street line.
     * @param string $part   Zip or city.
     *
     * @return string The part, or an empty string when it should be skipped.
     */
    private static function unlessAlreadyInStreet(string $street, string $part): string
    {
        if ($part === '' || ($street !== '' && str_contains($street, $part))) {
            return '';
        }

        return $part;
    }
}
