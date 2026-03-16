<?php

declare(strict_types=1);

namespace ScipLaravel\Laravel;

final readonly class CommandDefinition
{
    public function __construct(
        public string $sourcePath,
        public string $className,
        public string $classSymbol,
        public string $signature,
    ) {
    }
}
