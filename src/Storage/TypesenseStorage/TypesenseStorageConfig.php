<?php

namespace SchemaTransformer\Storage\TypesenseStorage;

use Override;
use Typesense\Client;

class TypesenseStorageConfig implements TypesenseStorageConfigInterface
{
    public function __construct(
        private Client $typesenseClient,
        private TypesenseCollection $collection,
        private ?array $clearStorageQueryParams = null,
        private ?string $collectionName = null,
    ) {
    }

    public function getClient(): Client
    {
        return $this->typesenseClient;
    }

    public function getCollection(): TypesenseCollection
    {
        return $this->collection;
    }

    /**
     * Typesense collection name. A custom name overrides the enum value.
     */
    public function getCollectionName(): string
    {
        if ($this->collectionName === null || $this->collectionName === '') {
            return $this->collection->value;
        }

        return $this->collectionName;
    }

    public function getClearStorageQueryParams(): ?array
    {
        return $this->clearStorageQueryParams;
    }
}
