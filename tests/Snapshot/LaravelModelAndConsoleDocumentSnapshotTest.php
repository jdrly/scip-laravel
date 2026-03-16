<?php

declare(strict_types=1);

namespace Tests\Snapshot;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;
use Tests\Support\SnapshotAssertions;

final class LaravelModelAndConsoleDocumentSnapshotTest extends TestCase
{
    use SnapshotAssertions;

    public function testLaravel12UserModelDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelModelAndConsoleDocumentSnapshotTest/testLaravel12UserModelDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel12-app', 'app/Models/User.php'),
        );
    }

    public function testLaravel12ConsoleDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelModelAndConsoleDocumentSnapshotTest/testLaravel12ConsoleDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel12-app', 'routes/console.php'),
        );
    }

    public function testLaravel12CommandDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelModelAndConsoleDocumentSnapshotTest/testLaravel12CommandDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel12-app', 'app/Console/Commands/SyncUsersCommand.php'),
        );
    }

    public function testLaravel13UserModelDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelModelAndConsoleDocumentSnapshotTest/testLaravel13UserModelDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel13-app', 'app/Models/User.php'),
        );
    }

    public function testLaravel13ConsoleDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelModelAndConsoleDocumentSnapshotTest/testLaravel13ConsoleDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel13-app', 'routes/console.php'),
        );
    }

    public function testLaravel13CommandDocumentMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelModelAndConsoleDocumentSnapshotTest/testLaravel13CommandDocumentMatchesSnapshot.json',
            FixtureIndexer::documentJson('laravel13-app', 'app/Console/Commands/SyncPhotosCommand.php'),
        );
    }
}
