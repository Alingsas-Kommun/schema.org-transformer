<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use SchemaTransformer\Transforms\TransformBase;

/**
 * Shared helper for Västsverige event mappers.
 */
abstract class AbstractVastsverigeEventMapper implements VastsverigeEventMapperInterface
{
    /**
     * @param TransformBase|null $transform Transform used to prefix identifiers.
     */
    public function __construct(private ?TransformBase $transform = null)
    {
    }

    /**
     * Apply this mapper to the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    abstract public function map(Event $event, array $data): Event;

    /**
     * Prefix a source id with the pipeline id prefix.
     *
     * @param string|int $value Source identifier.
     *
     * @return string
     */
    protected function formatId(string | int $value): string
    {
        if ($this->transform === null) {
            return (string) $value;
        }

        return $this->transform->formatId($value);
    }
}
