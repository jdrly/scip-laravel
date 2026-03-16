<?php

declare(strict_types=1);

namespace ScipLaravel\Project;

final readonly class ComposerProject
{
    public function __construct(
        public string $rootPath,
        public string $packageName,
        public string $vendorDir,
        public bool $hasComposerLock,
    ) {
    }
}
