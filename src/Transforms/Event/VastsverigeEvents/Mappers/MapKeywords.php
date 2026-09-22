<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use Municipio\Schema\Schema;

/**
 * Maps Västsverige tags to schema.org DefinedTerm keywords.
 */
class MapKeywords extends AbstractVastsverigeEventMapper
{
    /**
     * Map source tags onto the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        $tags = array_values(array_unique(array_filter(
            array_map(fn ($tag) => trim((string) $tag), $data['Tags'] ?? []),
            fn (string $tag) => $tag !== ''
        )));

        return $event->keywords(array_map(
            fn (string $tag) => Schema::definedTerm()
                ->name($tag)
                ->inDefinedTermSet(Schema::definedTermSet()->name('tags')),
            $tags
        ));
    }
}
