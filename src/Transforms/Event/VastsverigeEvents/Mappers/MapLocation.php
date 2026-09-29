<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use Municipio\Schema\Schema;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\VastsverigeContact;

/**
 * Maps Västsverige city, address and coordinates to schema.org Place.
 */
class MapLocation extends AbstractVastsverigeEventMapper
{
    /**
     * Map the source place onto the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        $place = $this->place($data);
        if ($place === null) {
            return $event->location([]);
        }

        return $event->location([$place]);
    }

    /**
     * Build a place when the source has a name, address or coordinates.
     *
     * @param array<string, mixed> $data Source event.
     *
     * @return \Municipio\Schema\Place|null
     */
    private function place(array $data): ?\Municipio\Schema\Place
    {
        $name      = trim((string) ($data['City'] ?? ''));
        $address   = VastsverigeContact::address(is_array($data['Contact'] ?? null) ? $data['Contact'] : []);
        $latitude  = $this->coordinate($data['Position']['lat'] ?? null);
        $longitude = $this->coordinate($data['Position']['lng'] ?? null);

        if ($name === '' && $address === null && $latitude === null && $longitude === null) {
            return null;
        }

        $place = Schema::place();
        if ($name !== '') {
            $place = $place->name($name);
        }
        if ($address !== null) {
            $place = $place->address($address);
        }
        if ($latitude !== null && $longitude !== null) {
            $place = $place->latitude($latitude)->longitude($longitude);
        }

        return $place;
    }

    /**
     * Read a coordinate, treating 0 as missing.
     *
     * The API uses latitude 0 and longitude 0 when no position is stored.
     *
     * @param mixed $value Source coordinate.
     *
     * @return float|null
     */
    private function coordinate(mixed $value): ?float
    {
        if (!is_numeric($value) || (float) $value === 0.0) {
            return null;
        }

        return (float) $value;
    }
}
