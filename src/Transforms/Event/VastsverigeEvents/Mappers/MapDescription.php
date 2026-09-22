<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;

/**
 * Maps a Västsverige tagline to schema.org Event description.
 */
class MapDescription extends AbstractVastsverigeEventMapper
{
    /**
     * Map the source tagline onto the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        $tagline = trim((string) ($data['Tagline'] ?? ''));

        return $event->description($tagline === '' ? [] : [$tagline]);
    }
}
