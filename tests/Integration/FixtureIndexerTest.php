<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;

final class FixtureIndexerTest extends TestCase
{
    public function testSummarizeReturnsPlainPhpFixtureManifest(): void
    {
        $summary = FixtureIndexer::summarize('plain-php-modern');

        self::assertStringContainsString('package: fixtures/plain-php-modern', $summary);
        self::assertStringContainsString('- src/ExampleClass.php (2 statements)', $summary);
    }
}
