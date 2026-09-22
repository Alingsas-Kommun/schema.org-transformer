<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use Municipio\Schema\Schema;

/**
 * Sets schema.org eventAttendanceMode. Source events are physical visits.
 */
class MapEventAttendanceMode extends AbstractVastsverigeEventMapper
{
    /**
     * Mark the event as an offline event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        unset($data);

        return $event->eventAttendanceMode(
            Schema::eventAttendanceModeEnumeration()::OfflineEventAttendanceMode
        );
    }
}
