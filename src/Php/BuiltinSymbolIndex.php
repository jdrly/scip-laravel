<?php

declare(strict_types=1);

namespace ScipLaravel\Php;

use JetBrains\PHPStormStub\PhpStormStubsMap;
use RuntimeException;

use function is_file;
use function realpath;

final class BuiltinSymbolIndex
{
    private string $stubsRoot;

    public function __construct(?string $stubsRoot = null)
    {
        $this->stubsRoot = $stubsRoot ?? dirname(__DIR__, 2) . '/vendor/jetbrains/phpstorm-stubs';
    }

    public function isClassLike(string $identifier): bool
    {
        return isset(PhpStormStubsMap::CLASSES[$identifier]);
    }

    public function isFunction(string $identifier): bool
    {
        return isset(PhpStormStubsMap::FUNCTIONS[$identifier]);
    }

    public function isConstant(string $identifier): bool
    {
        return isset(PhpStormStubsMap::CONSTANTS[$identifier]);
    }

    public function stubPathFor(string $identifier): ?string
    {
        $relativePath = PhpStormStubsMap::CLASSES[$identifier]
            ?? PhpStormStubsMap::FUNCTIONS[$identifier]
            ?? PhpStormStubsMap::CONSTANTS[$identifier]
            ?? null;

        if ($relativePath === null) {
            return null;
        }

        $stubPath = realpath($this->stubsRoot . '/' . $relativePath);
        if ($stubPath === false || !is_file($stubPath)) {
            throw new RuntimeException("Invalid stub path for identifier: $identifier.");
        }

        return $stubPath;
    }
}
