<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use Municipio\Schema\Schema;

/**
 * Sets schema.org eventStatus. The source has no cancelled or postponed flag.
 */
class MapEventStatus extends AbstractVastsverigeEventMapper
{
    /**
     * Mark the event as scheduled.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        unset($data);

        return $event->eventStatus(Schema::eventStatusType()::EventScheduled);
    }
}
