<?php

declare(strict_types=1);

namespace SchemaTransformer\Paginators;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class PathOffsetPaginatorTest extends TestCase
{
    #[TestDox('getNext() adds the page size to the offset in the path')]
    public function testAdvancesOffset(): void
    {
        $paginator = new PathOffsetPaginator();

        $this->assertSame(
            'https://www.vastsverige.com/api/get/sv/event/2671/100/100',
            $paginator->getNext('https://www.vastsverige.com/api/get/sv/event/2671/0/100', [])
        );
    }

    #[TestDox('getNext() keeps an existing query string')]
    public function testKeepsQueryString(): void
    {
        $paginator = new PathOffsetPaginator();

        $this->assertSame(
            'https://www.vastsverige.com/api/get/sv/event/2671/50/25?lang=sv',
            $paginator->getNext('https://www.vastsverige.com/api/get/sv/event/2671/25/25?lang=sv', [])
        );
    }

    #[TestDox('getNext() returns false when the path has no offset and limit')]
    public function testMissingOffset(): void
    {
        $paginator = new PathOffsetPaginator();

        $this->assertFalse($paginator->getNext('https://www.vastsverige.com/api/get/sv/event/2671', []));
    }

    #[TestDox('getNext() returns false when the page size is zero')]
    public function testZeroLimit(): void
    {
        $paginator = new PathOffsetPaginator();

        $this->assertFalse($paginator->getNext('https://www.vastsverige.com/api/get/sv/event/2671/0/0', []));
    }
}
