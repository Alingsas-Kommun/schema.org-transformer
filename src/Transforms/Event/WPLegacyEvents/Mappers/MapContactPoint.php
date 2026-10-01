<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\WPLegacyEvents\Mappers;

use Municipio\Schema\ContactPoint;
use Municipio\Schema\Event;
use Municipio\Schema\Schema;
use SchemaTransformer\Transforms\Event\WPLegacyEvents\Mappers\AbstractWPLegacyEventMapper;

class MapContactPoint extends AbstractWPLegacyEventMapper
{
    public function __construct()
    {
        parent::__construct();
    }

    public function map(Event $event, array $data): Event
    {
        $contactPoint = $this->tryMapContactPoint($data);
        if ($contactPoint === null) {
            return $event->contactPoint([]);
        }

        return $event->contactPoint([$contactPoint]);
    }

    /**
     * Build a contact point from event-level contact_* fields.
     *
     * @param array<string, mixed> $data Event REST item.
     */
    private function tryMapContactPoint(array $data): ?ContactPoint
    {
        $telephone    = $this->nonEmptyString($data['contact_phone'] ?? null);
        $email        = $this->nonEmptyString($data['contact_email'] ?? null);
        $description  = $this->nonEmptyString($data['contact_information'] ?? null);
        if ($telephone === null && $email === null && $description === null) {
            return null;
        }

        return Schema::contactPoint()
            ->telephone($telephone)
            ->email($email)
            ->description($description);
    }

    /**
     * @param mixed $value Raw field value.
     */
    private function nonEmptyString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        return $trimmed;
    }
}
