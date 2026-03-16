<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

final class BinarySmokeTest extends TestCase
{
    public function testBinaryEntryPointExists(): void
    {
        self::assertFileExists(__DIR__ . '/../../bin/scip-laravel');
    }
}
