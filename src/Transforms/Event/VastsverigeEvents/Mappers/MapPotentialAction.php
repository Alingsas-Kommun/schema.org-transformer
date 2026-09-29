<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use Municipio\Schema\Schema;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\VastsverigeUrl;

/**
 * Maps a Västsverige booking link to schema.org ReserveAction.
 */
class MapPotentialAction extends AbstractVastsverigeEventMapper
{
    /**
     * @param string $baseUrl Public site origin used for relative booking paths.
     */
    public function __construct(private string $baseUrl)
    {
        parent::__construct();
    }

    /**
     * Map the source booking link onto the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        $contact = is_array($data['Contact'] ?? null) ? $data['Contact'] : [];
        $url     = VastsverigeUrl::absolute(
            is_string($contact['BookingLink'] ?? null) ? $contact['BookingLink'] : null,
            $this->baseUrl
        );
        if ($url === null) {
            return $event->potentialAction([]);
        }

        return $event->potentialAction([
            Schema::action()
                ->type('ReserveAction')
                ->description('Boka')
                ->url($url),
        ]);
    }
}
