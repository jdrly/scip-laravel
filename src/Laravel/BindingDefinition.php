<?php

declare(strict_types=1);

namespace ScipLaravel\Laravel;

final readonly class BindingDefinition
{
    public function __construct(
        public string $sourcePath,
        public string $abstract,
        public string $concrete,
        public string $kind,
    ) {
    }
}
