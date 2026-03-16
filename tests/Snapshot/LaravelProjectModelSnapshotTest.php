<?php

declare(strict_types=1);

namespace Tests\Snapshot;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;
use Tests\Support\SnapshotAssertions;

final class LaravelProjectModelSnapshotTest extends TestCase
{
    use SnapshotAssertions;

    public function testLaravel12ProjectModelMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelProjectModelSnapshotTest/testLaravel12ProjectModelMatchesSnapshot.txt',
            FixtureIndexer::summarizeProjectModel('laravel12-app'),
        );
    }

    public function testLaravel13ProjectModelMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'LaravelProjectModelSnapshotTest/testLaravel13ProjectModelMatchesSnapshot.txt',
            FixtureIndexer::summarizeProjectModel('laravel13-app'),
        );
    }
}
