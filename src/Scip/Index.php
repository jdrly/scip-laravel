<?php

declare(strict_types=1);

namespace ScipLaravel\Scip;

use JsonException;

final readonly class Index implements \JsonSerializable
{
    /** @param list<Document> $documents */
    public function __construct(
        public Metadata $metadata,
        public array $documents,
    ) {
    }

    /** @return array{documents: list<Document>, metadata: Metadata} */
    public function jsonSerialize(): array
    {
        return [
            'documents' => $this->documents,
            'metadata' => $this->metadata,
        ];
    }

    public function toJson(): string
    {
        try {
            return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        } catch (JsonException $exception) {
            throw new \RuntimeException('Failed to encode index as JSON.', previous: $exception);
        }
    }
}
