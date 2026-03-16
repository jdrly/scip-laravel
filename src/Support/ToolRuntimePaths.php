<?php

declare(strict_types=1);

namespace ScipLaravel\Support;

use JetBrains\PHPStormStub\PhpStormStubsMap;
use ReflectionClass;
use RuntimeException;
use ScipLaravel\Cli\ApplicationFactory;

use function dirname;
use function is_dir;
use function is_file;
use function realpath;

final class ToolRuntimePaths
{
    public function packageRoot(): string
    {
        $applicationFactoryPath = (new ReflectionClass(ApplicationFactory::class))->getFileName();
        if ($applicationFactoryPath === false || !is_file($applicationFactoryPath)) {
            throw new RuntimeException('Unable to resolve tool package root.');
        }

        return $this->normalize(dirname($applicationFactoryPath, 3));
    }

    public function phpStormStubsRoot(): string
    {
        $stubMapPath = (new ReflectionClass(PhpStormStubsMap::class))->getFileName();
        if ($stubMapPath === false || !is_file($stubMapPath)) {
            throw new RuntimeException('Unable to resolve PhpStorm stubs root.');
        }

        $stubsRoot = dirname($stubMapPath);
        if (!is_dir($stubsRoot)) {
            throw new RuntimeException('Resolved PhpStorm stubs root is not a directory.');
        }

        return $this->normalize($stubsRoot);
    }

    private function normalize(string $path): string
    {
        $resolvedPath = realpath($path);

        return $resolvedPath === false ? $path : $resolvedPath;
    }
}
