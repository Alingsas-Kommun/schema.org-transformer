<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use Municipio\Schema\Event;
use Municipio\Schema\Schema;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\VastsverigeContact;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\VastsverigeUrl;

/**
 * Maps a Västsverige contact to schema.org Organization.
 */
class MapOrganizer extends AbstractVastsverigeEventMapper
{
    /**
     * @param string $baseUrl Public site origin used for relative website paths.
     */
    public function __construct(private string $baseUrl)
    {
        parent::__construct();
    }

    /**
     * Map the source contact onto the event.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        $contact = is_array($data['Contact'] ?? null) ? $data['Contact'] : [];
        $name    = trim((string) ($contact['CompanyName'] ?? ''));
        if ($name === '') {
            return $event->organizer([]);
        }

        $organization = Schema::organization()->name($name);
        $organization = $this->withText($organization, 'email', $contact['Email'] ?? null);
        $organization = $this->withText($organization, 'telephone', $contact['Phone'] ?? null);
        $organization = $this->withUrl($organization, $contact['Website'] ?? null);
        $organization = $this->withAddress($organization, $contact);

        return $event->organizer([$organization]);
    }

    /**
     * Set a trimmed text property when the source value is present.
     *
     * @param \Municipio\Schema\Organization $organization Organization being built.
     * @param string                         $property     Schema property name.
     * @param mixed                          $value        Source value.
     *
     * @return \Municipio\Schema\Organization
     */
    private function withText(\Municipio\Schema\Organization $organization, string $property, mixed $value): \Municipio\Schema\Organization
    {
        $text = trim((string) $value);
        if ($text === '') {
            return $organization;
        }

        return $organization->setProperty($property, $text);
    }

    /**
     * Set the organizer website when the source value is present.
     *
     * @param \Municipio\Schema\Organization $organization Organization being built.
     * @param mixed                          $value        Source website.
     *
     * @return \Municipio\Schema\Organization
     */
    private function withUrl(\Municipio\Schema\Organization $organization, mixed $value): \Municipio\Schema\Organization
    {
        $url = VastsverigeUrl::absolute(is_string($value) ? $value : null, $this->baseUrl);
        if ($url === null) {
            return $organization;
        }

        return $organization->url($url);
    }

    /**
     * Set the organizer address when the contact has address parts.
     *
     * @param \Municipio\Schema\Organization $organization Organization being built.
     * @param array<string, mixed>           $contact      Source contact.
     *
     * @return \Municipio\Schema\Organization
     */
    private function withAddress(\Municipio\Schema\Organization $organization, array $contact): \Municipio\Schema\Organization
    {
        $address = VastsverigeContact::address($contact);
        if ($address === null) {
            return $organization;
        }

        return $organization->address($address);
    }
}
