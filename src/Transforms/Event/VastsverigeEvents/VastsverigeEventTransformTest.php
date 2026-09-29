<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents;

use Municipio\Schema\Schema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(VastsverigeEventTransform::class)]
final class VastsverigeEventTransformTest extends TestCase
{
    #[TestDox('preprocessData returns Hits and drops values that are not objects')]
    public function testPreprocessData(): void
    {
        $transform = new VastsverigeEventTransform('vs-', 'https://www.vastsverige.com');

        $this->assertSame(
            [['Id' => 1]],
            $transform->preprocessData([
                'TotalCount' => 2,
                'Hits'       => [['Id' => 1], 'skip'],
            ])
        );
        $this->assertSame([], $transform->preprocessData(['TotalCount' => 0]));
    }

    #[TestDox('transform maps a Västsverige event to schema.org Event')]
    public function testTransform(): void
    {
        $expected = Schema::event()
            ->identifier('vs-635452')
            ->name('Årets Julbord')
            ->description(['Missa inte premiären'])
            ->startDate('2026-12-17T18:00:00')
            ->endDate('2026-12-18T22:30:00')
            ->url('https://www.vastsverige.com/alingsas/evenemang/arets-julbord/')
            ->image([
                Schema::imageObject()->url('https://www.vastsverige.com/contentassets/example.png'),
            ])
            ->location([
                Schema::place()
                    ->name('Alingsås')
                    ->address('Bryggaregatan 2, 441 30 Alingsås, Sverige')
                    ->latitude(57.9290825)
                    ->longitude(12.5340384),
            ])
            ->organizer([
                Schema::organization()
                    ->name('Estrad i Alingsås AB')
                    ->email('info@estradalingsas.se')
                    ->telephone('+46 322 644850')
                    ->url('https://www.estradalingsas.se')
                    ->address('Bryggaregatan 2, 441 30 Alingsås, Sverige'),
            ])
            ->keywords([
                Schema::definedTerm()
                    ->name('mattrv')
                    ->inDefinedTermSet(Schema::definedTermSet()->name('tags')),
            ])
            ->eventSchedule([
                Schema::schedule()
                    ->startDate('2026-12-17T00:00:00')
                    ->endDate(null),
            ])
            ->eventStatus(Schema::eventStatusType()::EventScheduled)
            ->eventAttendanceMode(Schema::eventAttendanceModeEnumeration()::OfflineEventAttendanceMode)
            ->potentialAction([
                Schema::action()
                    ->type('ReserveAction')
                    ->description('Boka')
                    ->url('https://estradalingsas.se/kalender/julbord-17/'),
            ])
            ->setProperty('x-created-by', VastsverigeEventTransform::CREATED_BY)
            ->toArray();

        $actual = (new VastsverigeEventTransform('vs-', 'https://www.vastsverige.com'))->transform([
            [
                'Id'       => 635452,
                'Heading'  => ' Årets Julbord ',
                'Tagline'  => 'Missa inte premiären',
                'Start'    => '2026-12-17T18:00:00+01:00',
                'End'      => '2026-12-18T22:30:00+01:00',
                'Url'      => '/alingsas/evenemang/arets-julbord/',
                'City'     => ' Alingsås ',
                'Image'    => ['Path' => 'https://www.vastsverige.com/contentassets/example.png'],
                'Tags'     => ['mattrv', 'mattrv', ' '],
                'Position' => ['lat' => 57.9290825, 'lng' => 12.5340384],
                'Contact'  => [
                    'CompanyName' => 'Estrad i Alingsås AB',
                    'Street'      => 'Bryggaregatan 2, 441 30 Alingsås, Sverige',
                    'City'        => 'Alingsås',
                    'Phone'       => '+46 322 644850',
                    'Email'       => 'info@estradalingsas.se',
                    'Website'     => 'www.estradalingsas.se',
                    'BookingLink' => 'https://estradalingsas.se/kalender/julbord-17/',
                ],
                'Dates'    => [
                    ['Date' => '2026-12-17T00:00:00+01:00', 'Times' => []],
                ],
            ],
        ])[0];

        $this->assertEquals($expected, $actual);
    }

    #[TestDox('transform keeps a stable document when most source fields are missing')]
    public function testSparseEvent(): void
    {
        $expected = Schema::event()
            ->identifier('vs-1')
            ->name(null)
            ->description([])
            ->startDate(null)
            ->endDate(null)
            ->url(null)
            ->image([])
            ->location([])
            ->organizer([])
            ->keywords([])
            ->eventSchedule([])
            ->eventStatus(Schema::eventStatusType()::EventScheduled)
            ->eventAttendanceMode(Schema::eventAttendanceModeEnumeration()::OfflineEventAttendanceMode)
            ->potentialAction([])
            ->setProperty('x-created-by', VastsverigeEventTransform::CREATED_BY)
            ->toArray();

        $actual = (new VastsverigeEventTransform('vs-', 'https://www.vastsverige.com'))->transform([
            ['Id' => 1],
        ])[0];

        $this->assertEquals($expected, $actual);
    }
}
