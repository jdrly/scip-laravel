<?php

declare(strict_types=1);

namespace Tests\Support;

trait SnapshotAssertions
{
    protected function assertMatchesSnapshot(string $relativeSnapshotPath, string $actual): void
    {
        $snapshotPath = FixturePaths::snapshot($relativeSnapshotPath);

        self::assertFileExists($snapshotPath);
        self::assertSame(FixturePaths::read($snapshotPath), $actual);
    }
}
