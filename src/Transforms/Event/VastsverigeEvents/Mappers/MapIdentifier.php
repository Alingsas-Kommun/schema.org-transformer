<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use SchemaTransformer\Transforms\TransformBase;

/**
 * Maps a Västsverige event id to schema.org identifier.
 */
class MapIdentifier extends AbstractVastsverigeEventMapper
{
    /**
     * @param TransformBase $transform Transform used to prefix identifiers.
     */
    public function __construct(TransformBase $transform)
    {
        parent::__construct($transform);
    }

    /**
     * Map the source id onto the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        $id = $data['Id'] ?? null;
        if ($id === null || $id === '') {
            return $event->identifier(null);
        }

        return $event->identifier($this->formatId((string) $id));
    }
}
