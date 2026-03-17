<?php

declare(strict_types=1);

namespace ScipLaravel\Release;

use RuntimeException;

use function strpos;
use function substr;
use function trim;

final class ChangelogReleaseNotes
{
    public function extract(string $changelog, string $version): string
    {
        $heading = '## [' . $version . ']';
        $start = strpos($changelog, $heading);
        if ($start === false) {
            throw new RuntimeException("Missing changelog section for version $version.");
        }

        $nextSection = strpos($changelog, "\n## [", $start + 1);
        $section = $nextSection === false
            ? substr($changelog, $start)
            : substr($changelog, $start, $nextSection - $start);

        return trim($section) . "\n";
    }
}
