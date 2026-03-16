<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;

final class LaravelProjectIndexingTest extends TestCase
{
    public function testLaravel12FixtureCanBeIndexed(): void
    {
        $json = FixtureIndexer::indexAsJson('laravel12-app');

        self::assertStringContainsString('"relativePath": "routes/web.php"', $json);
        self::assertStringContainsString('"relativePath": "app/Http/Controllers/HomeController.php"', $json);
        self::assertStringContainsString('App/Http/Controllers/HomeController#index().', $json);
    }

    public function testLaravel13FixtureCanBeIndexed(): void
    {
        $json = FixtureIndexer::indexAsJson('laravel13-app');

        self::assertStringContainsString('"relativePath": "routes/api.php"', $json);
        self::assertStringContainsString('"relativePath": "app/Providers/AppServiceProvider.php"', $json);
        self::assertStringContainsString('App/Models/Photo#', $json);
    }
}
