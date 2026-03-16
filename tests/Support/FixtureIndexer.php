<?php

declare(strict_types=1);

namespace Tests\Support;

use ScipLaravel\Core\ProjectIndexer;
use ScipLaravel\Php\Parser;
use ScipLaravel\Project\ComposerProjectReader;
use ScipLaravel\Project\ProjectFileFinder;
use ScipLaravel\Project\ProjectModelDetector;

use function implode;
use function str_replace;

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
        return self::indexProjectAsJson(FixturePaths::fixture($fixtureName), $fixtureName);
    }

    public static function indexProjectAsJson(string $projectPath, string $snapshotProjectName): string
    {
        $json = (new ProjectIndexer())->index($projectPath)->toJson();

        return str_replace(
            'file://' . $projectPath,
            'file://__FIXTURE_ROOT__/' . $snapshotProjectName,
            $json,
        );
    }

    public static function summarizeProjectModel(string $fixtureName): string
    {
        $projectModel = (new ProjectModelDetector())->detect(FixturePaths::fixture($fixtureName));

        $lines = [
            'fixture: ' . $fixtureName,
            'framework: ' . $projectModel->framework,
            'laravel-version: ' . ($projectModel->laravelVersion ?? 'none'),
            'has-bootstrap-app: ' . ($projectModel->hasBootstrapApp ? 'yes' : 'no'),
            'has-bootstrap-providers: ' . ($projectModel->hasBootstrapProviders ? 'yes' : 'no'),
            'route-files: ' . implode(', ', $projectModel->routeFiles),
            'provider-files: ' . implode(', ', $projectModel->providerFiles),
            'controller-directories: ' . implode(', ', $projectModel->controllerDirectories),
            'model-directories: ' . implode(', ', $projectModel->modelDirectories),
            'command-directories: ' . implode(', ', $projectModel->commandDirectories),
        ];

        return implode("\n", $lines) . "\n";
    }
}
