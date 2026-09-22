<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\Tests;

use Municipio\Schema\Event;
use Municipio\Schema\Schema;
use PHPUnit\Framework\Assert;
use SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers\VastsverigeEventMapperInterface;

/**
 * Asserts that a Västsverige mapper converts a JSON source into an event.
 */
class TestHelper extends Assert
{
    /**
     * Map a JSON source and compare it with the expected event.
     *
     * @param VastsverigeEventMapperInterface $mapper        Mapper under test.
     * @param string                          $sourceJson    Source event as JSON.
     * @param Event                           $expectedEvent Expected event.
     * @param string|null                     $message       Assertion message.
     *
     * @return self
     */
    public function expectMapperToConvertSourceTo(
        VastsverigeEventMapperInterface $mapper,
        string $sourceJson,
        Event $expectedEvent,
        ?string $message = null
    ): self {
        $source = json_decode($sourceJson, true);
        $this->assertIsArray($source, 'Source data is empty or invalid JSON');

        $actual = $mapper->map(Schema::event(), $source);

        $this->assertEquals(
            $expectedEvent->toArray(),
            $actual->toArray(),
            $message ?: 'Mapper did not produce expected output for given source'
        );

        return $this;
    }
}
