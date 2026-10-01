<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\WPLegacyEvents\Mappers\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\CoversClass;
use Municipio\Schema\Schema;
use SchemaTransformer\Transforms\Event\WPLegacyEvents\Mappers\Tests\TestHelper;
use SchemaTransformer\Transforms\Event\WPLegacyEvents\Mappers\MapContactPoint;

#[CoversClass(MapContactPoint::class)]
final class MapContactPointTest extends TestCase
{
    #[TestDox('event::contactPoint is mapped from contact_phone, contact_email and contact_information')]
    public function testItWorks()
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapContactPoint(),
            '{
                "contact_phone": "+46 735141562",
                "contact_email": "franskatesalongen@gmail.com",
                "contact_information": "Ring före besök"
            }',
            Schema::event()->contactPoint([
                Schema::contactPoint()
                    ->telephone('+46 735141562')
                    ->email('franskatesalongen@gmail.com')
                    ->description('Ring före besök'),
            ])
        );
    }

    #[TestDox('event::contactPoint([]) when contact fields are missing')]
    public function testHandlesMissingContact()
    {
        (new TestHelper())->expectMapperToConvertSourceTo(
            new MapContactPoint(),
            '{"id": 123}',
            Schema::event()->contactPoint([])
        );
    }
}
