<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;

final class LaravelProviderAnalyzerTest extends TestCase
{
    public function testLaravel12BootstrapProvidersReferenceRegisteredProvider(): void
    {
        $document = FixtureIndexer::documentJson('laravel12-app', 'bootstrap/providers.php');

        self::assertStringContainsString('App/Providers/AppServiceProvider#', $document);
    }

    public function testLaravel12ProviderDocumentReferencesBoundClasses(): void
    {
        $document = FixtureIndexer::documentJson('laravel12-app', 'app/Providers/AppServiceProvider.php');

        self::assertStringContainsString('App/Models/User#', $document);
        self::assertStringContainsString('App/Models/Post#', $document);
    }

    public function testLaravel13ProviderDocumentReferencesBoundClasses(): void
    {
        $document = FixtureIndexer::documentJson('laravel13-app', 'app/Providers/AppServiceProvider.php');

        self::assertStringContainsString('App/Models/User#', $document);
        self::assertStringContainsString('App/Models/Photo#', $document);
    }
}
