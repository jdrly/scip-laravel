<?php

declare(strict_types=1);

namespace Tests\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ScipLaravel\Php\Parser;
use ScipLaravel\Project\ComposerProjectReader;
use SplFileInfo;

use function array_values;
use function sort;

final class FixtureIndexer
{
    public static function summarize(string $fixtureName): string
    {
        $rootPath = FixturePaths::fixture($fixtureName);
        $project = (new ComposerProjectReader())->read($rootPath);
        $parser = new Parser();
        $phpFiles = self::phpFiles($rootPath);

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

    /** @return list<string> */
    private static function phpFiles(string $rootPath): array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath));
        $phpFiles = [];

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $realPath = $file->getRealPath();
            if ($realPath === false || str_contains($realPath, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $phpFiles[] = $realPath;
        }

        sort($phpFiles);

        return $phpFiles;
    }
}
