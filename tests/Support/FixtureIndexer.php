<?php

declare(strict_types=1);

namespace Tests\Support;

use ScipLaravel\Core\ProjectIndexer;
use ScipLaravel\Php\Parser;
use ScipLaravel\Project\ComposerProjectReader;
use ScipLaravel\Project\ProjectFileFinder;

final class FixtureIndexer
{
    public static function summarize(string $fixtureName): string
    {
        $rootPath = FixturePaths::fixture($fixtureName);
        $project = (new ComposerProjectReader())->read($rootPath);
        $parser = new Parser();
        $phpFiles = (new ProjectFileFinder())->phpFiles($project);

        $lines = [
            'fixture: ' . $fixtureName,
            'package: ' . $project->packageName,
            'vendor-dir: ' . $project->vendorDir,
            'has-composer-lock: ' . ($project->hasComposerLock ? 'yes' : 'no'),
            'php-files:',
        ];

        foreach ($phpFiles as $phpFile) {
            $relativePath = substr($phpFile, strlen($rootPath) + 1);
            $statementCount = count($parser->parseFile($phpFile));
            $lines[] = '- ' . $relativePath . ' (' . $statementCount . ' statements)';
        }

        return implode("\n", $lines) . "\n";
    }

    public static function indexAsJson(string $fixtureName): string
    {
        return (new ProjectIndexer())->index(FixturePaths::fixture($fixtureName))->toJson();
    }
}
