<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use Municipio\Schema\Schema;

/**
 * Maps a Västsverige image path to schema.org ImageObject.
 */
class MapImage extends AbstractVastsverigeEventMapper
{
    /**
     * Map the source image onto the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        $path = trim((string) ($data['Image']['Path'] ?? ''));
        if ($path === '') {
            return $event->image([]);
        }

        return $event->image([
            Schema::imageObject()->url($path),
        ]);
    }
}
