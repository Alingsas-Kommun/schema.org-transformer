<?php

declare(strict_types=1);

namespace SchemaTransformer\Interfaces;

interface AbstractXMLDataTransform
{
	public function transform(string $xmlString): array;
}
