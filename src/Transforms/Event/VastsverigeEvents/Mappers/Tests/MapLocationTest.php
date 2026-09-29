<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\Tests;

use Municipio\Schema\Schema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapLocation;

#[CoversClass(MapLocation::class)]
final class MapLocationTest extends TestCase
{
    #[TestDox('event::location uses city, contact address and coordinates')]
    public function testPlaceWithCoordinates(): void
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapLocation(),
            '{
                "City": " Alingsås ",
                "Position": {"lat": 57.9290825, "lng": 12.5340384},
                "Contact": {
                    "Street": "Kungsallén 35",
                    "Zip": "46695",
                    "City": "Sollebrunn"
                }
            }',
            Schema::event()->location([
                Schema::place()
                    ->name('Alingsås')
                    ->address('Kungsallén 35, 46695, Sollebrunn')
                    ->latitude(57.9290825)
                    ->longitude(12.5340384),
            ])
        );
    }

    #[TestDox('event::location omits coordinates when the API sends 0,0')]
    public function testMissingCoordinates(): void
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapLocation(),
            '{
                "City": "Alingsås",
                "Position": {"lat": 0, "lng": 0},
                "Contact": {
                    "Street": "Bryggaregatan 2, 441 30 Alingsås, Sverige",
                    "City": "Alingsås"
                }
            }',
            Schema::event()->location([
                Schema::place()
                    ->name('Alingsås')
                    ->address('Bryggaregatan 2, 441 30 Alingsås, Sverige'),
            ])
        );
    }

    #[TestDox('event::location([]) when place data is missing')]
    public function testMissing(): void
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapLocation(),
            '{
                "Id": 1
            }',
            Schema::event()->location([])
        );
    }
}
