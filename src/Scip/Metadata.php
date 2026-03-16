<?php

declare(strict_types=1);

namespace ScipLaravel\Scip;

final readonly class Metadata implements \JsonSerializable
{
    public function __construct(
        public string $projectRoot,
        public string $toolName,
        public string $toolVersion,
    ) {
    }

    /** @return array{projectRoot: string, toolName: string, toolVersion: string} */
    public function jsonSerialize(): array
    {
        return [
            'projectRoot' => $this->projectRoot,
            'toolName' => $this->toolName,
            'toolVersion' => $this->toolVersion,
        ];
    }
}
