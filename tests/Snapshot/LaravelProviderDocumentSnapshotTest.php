<?php

declare(strict_types=1);

namespace Tests\Snapshot;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;
use Tests\Support\SnapshotAssertions;

final class LaravelProviderDocumentSnapshotTest extends TestCase
{
    use SnapshotAssertions;

    public function testLaravel12BootstrapProvidersDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelProviderDocumentSnapshotTest/testLaravel12BootstrapProvidersDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel12-app', 'bootstrap/providers.php'),
        );
    }

    public function testLaravel12ProviderDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelProviderDocumentSnapshotTest/testLaravel12ProviderDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel12-app', 'app/Providers/AppServiceProvider.php'),
        );
    }

    public function testLaravel13ProviderDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelProviderDocumentSnapshotTest/testLaravel13ProviderDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel13-app', 'app/Providers/AppServiceProvider.php'),
        );
    }
}
