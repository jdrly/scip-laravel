<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;
use Tests\Support\TemporaryProject;

final class ExternalProjectIndexingTest extends TestCase
{
    public function testProjectIndexerWorksForFixtureCopiedOutsideRepository(): void
    {
        $temporaryProjectPath = TemporaryProject::copyFixture('plain-php-modern');

        try {
            $json = FixtureIndexer::indexProjectAsJson($temporaryProjectPath, 'external-plain-php-modern');
        } finally {
            TemporaryProject::remove($temporaryProjectPath);
        }

        self::assertStringContainsString('"toolName": "scip-laravel"', $json);
        self::assertStringContainsString('"relativePath": "src/ExampleClass.php"', $json);
        self::assertStringContainsString('file://__FIXTURE_ROOT__/external-plain-php-modern', $json);
    }
}
