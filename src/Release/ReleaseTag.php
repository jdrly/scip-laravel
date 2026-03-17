<?php

declare(strict_types=1);

namespace ScipLaravel\Release;

use RuntimeException;

final class ReleaseTag
{
    public function versionFromTag(string $tag): string
    {
        if (preg_match('/^v(?<version>\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?)$/', $tag, $matches) !== 1) {
            throw new RuntimeException('Release tags must use SemVer format like v1.2.3 or v1.2.3-rc.1.');
        }

        return $matches['version'];
    }

    public function assertMatchesApplicationVersion(string $tag, string $applicationVersion): string
    {
        $version = $this->versionFromTag($tag);
        if ($version !== $applicationVersion) {
            throw new RuntimeException(
                "Release tag version $version does not match application version $applicationVersion.",
            );
        }

        return $version;
    }
}
