<?php

declare(strict_types=1);

namespace Tests\Snapshot;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;
use Tests\Support\SnapshotAssertions;

final class PlainPhpFixtureManifestTest extends TestCase
{
    use SnapshotAssertions;

    public function testPlainPhpFixtureManifestMatchesSnapshot(): void
    {
        $this->assertMatchesSnapshot(
            'PlainPhpFixtureManifestTest/testPlainPhpFixtureManifestMatchesSnapshot.txt',
            FixtureIndexer::summarize('plain-php-modern'),
        );
    }
}
