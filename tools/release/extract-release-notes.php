#!/usr/bin/env php
<?php

declare(strict_types=1);

use ScipLaravel\Release\ChangelogReleaseNotes;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$version = $argv[1] ?? null;
if (!is_string($version) || $version === '') {
    throw new RuntimeException('Usage: extract-release-notes.php <version>');
}

$changelogPath = dirname(__DIR__, 2) . '/CHANGELOG.md';
$changelog = file_get_contents($changelogPath);
if ($changelog === false) {
    throw new RuntimeException("Cannot read changelog: $changelogPath.");
}

fwrite(STDOUT, (new ChangelogReleaseNotes())->extract($changelog, $version));
