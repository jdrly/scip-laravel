<?php

declare(strict_types=1);

namespace Tests\Snapshot;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;
use Tests\Support\SnapshotAssertions;

final class PlainPhpFixtureIndexTest extends TestCase
{
    use SnapshotAssertions;

    public function testPlainPhpFixtureIndexMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'PlainPhpFixtureIndexTest/testPlainPhpFixtureIndexMatchesSnapshot.json',
            FixtureIndexer::indexAsJson('plain-php-modern'),
        );
    }
}
