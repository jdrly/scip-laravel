<?php

declare(strict_types=1);

namespace Tests\Support;

use ScipLaravel\Support\FileReader;

final class FixturePaths
{
    public static function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function fixture(string $name): string
    {
        return self::projectRoot() . '/fixtures/' . $name;
    }

    public static function snapshot(string $relativePath): string
    {
        return __DIR__ . '/../Snapshot/__snapshots__/' . $relativePath;
    }

    public static function testData(string $relativePath): string
    {
        return __DIR__ . '/../' . ltrim($relativePath, '/');
    }

    public static function read(string $path): string
    {
        return FileReader::read($path);
    }
}
