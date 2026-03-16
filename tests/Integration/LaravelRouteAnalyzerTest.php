<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;

final class LaravelRouteAnalyzerTest extends TestCase
{
    public function testLaravel12RoutesReferenceControllerMethods(): void
    {
        $webDocument = FixtureIndexer::documentJson('laravel12-app', 'routes/web.php');

        self::assertStringContainsString('App/Http/Controllers/HomeController#index().', $webDocument);
        self::assertStringContainsString('App/Http/Controllers/HomeController#about().', $webDocument);
        self::assertStringContainsString('App/Http/Controllers/HealthCheckController#__invoke().', $webDocument);
        self::assertStringContainsString('App/Http/Controllers/PhotoController#show().', $webDocument);
    }

    public function testLaravel13RoutesReferenceControllerMethods(): void
    {
        $webDocument = FixtureIndexer::documentJson('laravel13-app', 'routes/web.php');
        $apiDocument = FixtureIndexer::documentJson('laravel13-app', 'routes/api.php');

        self::assertStringContainsString('App/Http/Controllers/HomeController#about().', $webDocument);
        self::assertStringContainsString('App/Http/Controllers/PhotoController#destroy().', $webDocument);
        self::assertStringContainsString('App/Http/Controllers/PhotoController#update().', $apiDocument);
    }
}
