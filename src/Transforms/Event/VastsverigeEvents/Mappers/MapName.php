<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;

/**
 * Maps a Västsverige heading to schema.org Event name.
 */
class MapName extends AbstractVastsverigeEventMapper
{
    /**
     * Map the source heading onto the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        $heading = trim((string) ($data['Heading'] ?? ''));

        return $event->name($heading === '' ? null : $heading);
    }
}
