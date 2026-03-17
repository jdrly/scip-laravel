<?php

declare(strict_types=1);

namespace Tests\Unit\Release;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ScipLaravel\Release\ReleaseTag;

final class ReleaseTagTest extends TestCase
{
    private ReleaseTag $releaseTag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->releaseTag = new ReleaseTag();
    }

    public function testVersionFromTagAcceptsStableAndPrereleaseTags(): void
    {
        self::assertSame('1.2.3', $this->releaseTag->versionFromTag('v1.2.3'));
        self::assertSame('1.2.3-rc.1', $this->releaseTag->versionFromTag('v1.2.3-rc.1'));
    }

    public function testVersionFromTagRejectsNonSemverTags(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Release tags must use SemVer format');

        $this->releaseTag->versionFromTag('release-1.2.3');
    }

    public function testAssertMatchesApplicationVersionRejectsMismatches(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not match application version');

        $this->releaseTag->assertMatchesApplicationVersion('v1.2.3', '1.2.4');
    }
}
