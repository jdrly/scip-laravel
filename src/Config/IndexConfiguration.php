<?php

declare(strict_types=1);

namespace ScipLaravel\Config;

final readonly class IndexConfiguration
{
    public function __construct(
        public string $projectDir,
        public string $outputPath,
        public string $framework,
        public string $phpVersion,
        public string $memoryLimit,
    ) {
    }
}
