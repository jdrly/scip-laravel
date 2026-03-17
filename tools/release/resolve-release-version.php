#!/usr/bin/env php
<?php

declare(strict_types=1);

use ScipLaravel\Cli\ApplicationFactory;
use ScipLaravel\Release\ReleaseTag;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$tag = $argv[1] ?? null;
if (!is_string($tag) || $tag === '') {
    throw new RuntimeException('Usage: resolve-release-version.php <git-tag>');
}

$version = (new ReleaseTag())->assertMatchesApplicationVersion($tag, ApplicationFactory::VERSION);

fwrite(STDOUT, $version . PHP_EOL);
