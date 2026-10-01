<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\WPLegacyEvents\Mappers;

use Municipio\Schema\Schema;
use SchemaTransformer\Transforms\Event\WPLegacyEvents\Mappers\AbstractWPLegacyEventMapper;
use Municipio\Schema\Event;
use Municipio\Schema\Organization;

class MapOrganizer extends AbstractWPLegacyEventMapper
{
    public function __construct()
    {
        parent::__construct();
    }

    public function map(Event $event, array $data): Event
    {
        return $event->organizer(
            array_values(
                array_filter(
                    array_map(
                        fn ($organizer) => is_array($organizer) ? $this->mapOrganizer($organizer) : null,
                        $this->sourceOrganizers($data)
                    )
                )
            )
        );
    }

    /**
     * Prefer top-level organizers (api-event-manager 1.5.x). Fall back to _embedded.
     *
     * @param array<string, mixed> $data Event REST item.
     *
     * @return array<int, mixed>
     */
    private function sourceOrganizers(array $data): array
    {
        $topLevel = $data['organizers'] ?? null;
        if (is_array($topLevel) && $topLevel !== []) {
            return array_values($topLevel);
        }

        $embedded = $data['_embedded']['organizers'] ?? [];
        if (!is_array($embedded)) {
            return [];
        }

        return array_values($embedded);
    }

    /**
     * Map one organizer record, or null when embed is forbidden / name is missing.
     *
     * @param array<string, mixed> $organizer Source organizer.
     */
    private function mapOrganizer(array $organizer): ?Organization
    {
        if (($organizer['code'] ?? null) === 'rest_forbidden') {
            return null;
        }

        $name = $organizer['organizer'] ?? $organizer['title']['rendered'] ?? null;
        if ($name === null || $name === '') {
            return null;
        }

        return Schema::organization()
            ->name($name)
            ->url($organizer['organizer_link'] ?? $organizer['website'] ?? null)
            ->email($organizer['organizer_email'] ?? $organizer['email'] ?? null)
            ->telephone($organizer['organizer_phone'] ?? $organizer['phone'] ?? null);
    }
}
