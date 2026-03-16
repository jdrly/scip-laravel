<?php

declare(strict_types=1);

namespace Tests\Snapshot;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;
use Tests\Support\SnapshotAssertions;

final class LaravelRouteDocumentSnapshotTest extends TestCase
{
    use SnapshotAssertions;

    public function testLaravel12WebRouteDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelRouteDocumentSnapshotTest/testLaravel12WebRouteDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel12-app', 'routes/web.php'),
        );
    }

    public function testLaravel12ApiRouteDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelRouteDocumentSnapshotTest/testLaravel12ApiRouteDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel12-app', 'routes/api.php'),
        );
    }

    public function testLaravel13WebRouteDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelRouteDocumentSnapshotTest/testLaravel13WebRouteDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel13-app', 'routes/web.php'),
        );
    }

    public function testLaravel13ApiRouteDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelRouteDocumentSnapshotTest/testLaravel13ApiRouteDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel13-app', 'routes/api.php'),
        );
    }
}
