<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\Tests;

use Municipio\Schema\Schema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\MapEventSchedule;

#[CoversClass(MapEventSchedule::class)]
final class MapEventScheduleTest extends TestCase
{
    #[TestDox('event::eventSchedule uses the date timestamp when no clock times are given')]
    public function testDateWithoutTimes(): void
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapEventSchedule(),
            '{
                "Dates": [
                    {"Date": "2026-12-17T11:00:00+01:00"}
                ]
            }',
            Schema::event()->eventSchedule([
                Schema::schedule()
                    ->startDate('2026-12-17T11:00:00')
                    ->endDate(null),
            ])
        );
    }

    #[TestDox('event::eventSchedule applies clock times to the calendar day and sorts the result')]
    public function testClockTimesAreSorted(): void
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapEventSchedule(),
            '{
                "Dates": [
                    {
                        "Date": "2026-12-17T00:00:00+01:00",
                        "Times": []
                    },
                    {
                        "Date": "2026-02-26T00:00:00+01:00",
                        "Times": [{"Start": "19:00", "End": "22:00"}]
                    }
                ]
            }',
            Schema::event()->eventSchedule([
                Schema::schedule()
                    ->startDate('2026-02-26T19:00:00')
                    ->endDate('2026-02-26T22:00:00'),
                Schema::schedule()
                    ->startDate('2026-12-17T00:00:00')
                    ->endDate(null),
            ])
        );
    }

    #[TestDox('event::eventSchedule rolls an end clock that is not later than the start clock to the next day')]
    public function testEndClockRollsToNextDay(): void
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapEventSchedule(),
            '{
                "Dates": [
                    {
                        "Date": "2026-09-26T00:00:00+02:00",
                        "EndDate": "2026-09-27T00:00:00+02:00",
                        "Times": [{"Start": "17:00", "End": "00:00"}]
                    }
                ]
            }',
            Schema::event()->eventSchedule([
                Schema::schedule()
                    ->startDate('2026-09-26T17:00:00')
                    ->endDate('2026-09-27T00:00:00'),
            ])
        );
    }

    #[TestDox('event::eventSchedule([]) when Dates is missing')]
    public function testMissing(): void
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapEventSchedule(),
            '{
                "Id": 1
            }',
            Schema::event()->eventSchedule([])
        );
    }
}
