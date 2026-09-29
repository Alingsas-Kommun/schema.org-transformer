<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use SchemaTransformer\Util\DateUtils;

/**
 * Maps a Västsverige end timestamp to schema.org Event endDate.
 */
class MapEndDate extends AbstractVastsverigeEventMapper
{
    /**
     * Map the source end timestamp onto the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        return $event->endDate(DateUtils::toLocalDate($data['End'] ?? null));
    }
}
