<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;

/**
 * Maps one Västsverige event field onto a schema.org Event.
 */
interface VastsverigeEventMapperInterface
{
    /**
     * Apply this mapper to the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event;
}
