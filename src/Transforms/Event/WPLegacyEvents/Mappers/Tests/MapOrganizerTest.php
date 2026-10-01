<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\WPLegacyEvents\Mappers\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\CoversClass;
use Municipio\Schema\Schema;
use SchemaTransformer\Transforms\Event\WPLegacyEvents\Mappers\Tests\TestHelper;
use SchemaTransformer\Transforms\Event\WPLegacyEvents\Mappers\MapOrganizer;

#[CoversClass(MapOrganizer::class)]
final class MapOrganizerTest extends TestCase
{
    #[TestDox('event::organizer is mapped from organizer.rendered')]
    public function testItWorks()
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapOrganizer(),
            '{
                "_embedded": {
                    "organizers": [
                    {
                        "title": {
                        "rendered": "Test organizer",
                        "plain_text": "Test organizer"
                        },
                        "phone": "123-456 78 90",
                        "email": "organizer@example.com",
                        "website": "https://test-organizer-website.com"
                    },
                    {
                        "title": {
                        "rendered": "Test organizer 2",
                        "plain_text": "Test organizer 2"
                        },
                        "phone": "234-567 89 01",
                        "email": "organizer2@example.com",
                        "website": "https://test-organizer2-website.com"
                    }
                    ]
                }   
            }',
            Schema::event()->organizer([
                Schema::organization()
                    ->name('Test organizer')
                    ->telephone('123-456 78 90')
                    ->email('organizer@example.com')
                    ->url('https://test-organizer-website.com'),
                Schema::organization()
                    ->name('Test organizer 2')
                    ->telephone('234-567 89 01')
                    ->email('organizer2@example.com')
                    ->url('https://test-organizer2-website.com')
            ])
        );
    }

    #[TestDox('event::organizer is mapped from top-level organizers when embed is forbidden')]
    public function testMapsTopLevelOrganizers()
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapOrganizer(),
            '{
                "organizers": [
                    {
                        "main_organizer": true,
                        "organizer": "Franska Tesalongen",
                        "organizer_link": "http://franskatesalongen.se/",
                        "organizer_phone": "073-514 15 62",
                        "organizer_email": "franskatesalongen@gmail.com"
                    }
                ],
                "_embedded": {
                    "organizers": [
                        {
                            "code": "rest_forbidden",
                            "message": "Du har inte behörighet att göra detta.",
                            "data": { "status": 401 }
                        }
                    ]
                }
            }',
            Schema::event()->organizer([
                Schema::organization()
                    ->name('Franska Tesalongen')
                    ->telephone('073-514 15 62')
                    ->email('franskatesalongen@gmail.com')
                    ->url('http://franskatesalongen.se/'),
            ])
        );
    }

    #[TestDox('event::organizer([]) when no organizers are present')]
    public function testHandlesMissingOrganizers()
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapOrganizer(),
            '{"id": 123}',
            Schema::event()->organizer([])
        );
    }

    #[TestDox('event::organizer([]) when embedded organizers are rest_forbidden')]
    public function testIgnoresForbiddenEmbeddedOrganizers()
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapOrganizer(),
            '{
                "organizers": null,
                "_embedded": {
                    "organizers": [
                        {
                            "code": "rest_forbidden",
                            "data": { "status": 401 }
                        }
                    ]
                }
            }',
            Schema::event()->organizer([])
        );
    }
}
