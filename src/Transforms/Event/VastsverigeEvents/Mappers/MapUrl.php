<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\VastsverigeUrl;

/**
 * Maps a Västsverige event path to schema.org Event url.
 */
class MapUrl extends AbstractVastsverigeEventMapper
{
    /**
     * @param string $baseUrl Public site origin used for relative paths.
     */
    public function __construct(private string $baseUrl)
    {
        parent::__construct();
    }

    /**
     * Map the source path onto the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        return $event->url(VastsverigeUrl::absolute($data['Url'] ?? null, $this->baseUrl));
    }
}
