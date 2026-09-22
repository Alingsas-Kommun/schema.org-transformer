<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents;

use Municipio\Schema\Schema;
use SchemaTransformer\Interfaces\AbstractDataTransform;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapDescription;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapEndDate;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapEventAttendanceMode;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapEventSchedule;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapEventStatus;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapIdentifier;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapImage;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapKeywords;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapLocation;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapName;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapOrganizer;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapPotentialAction;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapStartDate;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapUrl;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapXCreatedBy;
use SchemaTransformer\Transforms\TransformBase;

/**
 * Transforms Västsverige event hits into schema.org Event documents.
 */
class VastsverigeEventTransform extends TransformBase implements AbstractDataTransform
{
    public const CREATED_BY = 'municipio://schema.org-transformer/vastsverige-events';

    /**
     * @param string $idprefix Prefix stored on each event identifier.
     * @param string $baseUrl  Public site origin used for relative paths.
     */
    public function __construct(string $idprefix, private string $baseUrl)
    {
        parent::__construct($idprefix);
    }

    /**
     * Extract event hits from a Västsverige list response.
     *
     * @param array<string, mixed> $data Decoded API response.
     *
     * @return array<int, array<string, mixed>>
     */
    public function preprocessData(array $data): array
    {
        $hits = $data['Hits'] ?? null;
        if (!is_array($hits)) {
            return [];
        }

        return array_values(array_filter($hits, 'is_array'));
    }

    /**
     * Transform source events into schema.org Event arrays.
     *
     * @param array<int, array<string, mixed>> $data Source events.
     *
     * @return array<int, array<string, mixed>>
     */
    public function transform(array $data): array
    {
        $mappers = [
            new MapIdentifier($this),
            new MapName(),
            new MapDescription(),
            new MapStartDate(),
            new MapEndDate(),
            new MapUrl($this->baseUrl),
            new MapImage(),
            new MapLocation(),
            new MapOrganizer($this->baseUrl),
            new MapKeywords(),
            new MapEventSchedule(),
            new MapEventStatus(),
            new MapEventAttendanceMode(),
            new MapPotentialAction($this->baseUrl),
            new MapXCreatedBy(),
        ];

        return array_map(
            fn ($item) => array_reduce(
                $mappers,
                fn ($event, $mapper) => $mapper->map($event, $item),
                Schema::event()
            )->toArray(),
            array_values($data)
        );
    }
}
