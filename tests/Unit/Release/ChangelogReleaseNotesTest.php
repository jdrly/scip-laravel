<?php

declare(strict_types=1);

namespace Tests\Unit\Release;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ScipLaravel\Release\ChangelogReleaseNotes;

final class ChangelogReleaseNotesTest extends TestCase
{
    private ChangelogReleaseNotes $releaseNotes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->releaseNotes = new ChangelogReleaseNotes();
    }

    public function testExtractReturnsRequestedVersionSection(): void
    {
        $changelog = <<<'MARKDOWN'
# Changelog

## [Unreleased]

### Added
- Work in progress.

## [1.2.3] - 2026-03-17

### Added
- Stable release note.

## [1.2.2] - 2026-03-10

### Fixed
- Earlier note.
MARKDOWN;

        self::assertSame(
            "## [1.2.3] - 2026-03-17\n\n### Added\n- Stable release note.\n",
            $this->releaseNotes->extract($changelog, '1.2.3'),
        );
    }

    public function testExtractFailsWhenVersionSectionIsMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing changelog section for version 1.2.3.');

        $this->releaseNotes->extract("## [Unreleased]\n", '1.2.3');
    }
}
