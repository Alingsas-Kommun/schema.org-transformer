<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\VastsverigeEventTransform;

/**
 * Marks documents produced by the Västsverige event pipeline.
 */
class MapXCreatedBy extends AbstractVastsverigeEventMapper
{
    /**
     * Set the pipeline source on the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        unset($data);

        return $event->setProperty('x-created-by', VastsverigeEventTransform::CREATED_BY);
    }
}
