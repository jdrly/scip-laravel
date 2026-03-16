<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;

final class ProjectIndexerTest extends TestCase
{
    public function testPlainPhpFixtureProducesScipLaravelIndexJson(): void
    {
        $json = FixtureIndexer::indexAsJson('plain-php-modern');

        self::assertStringContainsString('"toolName": "scip-laravel"', $json);
        self::assertStringContainsString('"relativePath": "src/ExampleClass.php"', $json);
        self::assertStringContainsString('Fixture/PlainPhp/HelperService#format().', $json);
    }
}
