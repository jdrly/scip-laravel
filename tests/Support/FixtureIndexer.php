<?php

declare(strict_types=1);

namespace Tests\Support;

use JsonException;
use ScipLaravel\Core\ProjectIndexer;
use ScipLaravel\Php\Parser;
use ScipLaravel\Project\ComposerProjectReader;
use ScipLaravel\Project\ProjectFileFinder;
use ScipLaravel\Project\ProjectModelDetector;

use function implode;
use function is_array;
use function json_decode;
use function json_encode;
use function realpath;
use function str_replace;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

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
        $resolvedProjectPath = realpath($projectPath);
        $normalizedProjectPath = $resolvedProjectPath === false
            ? $projectPath
            : $resolvedProjectPath;

        return str_replace(
            'file://' . $normalizedProjectPath,
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

    public static function documentJson(string $fixtureName, string $relativePath): string
    {
        $index = self::decodeIndex(self::indexAsJson($fixtureName));

        foreach ($index['documents'] as $document) {
            if (($document['relativePath'] ?? null) !== $relativePath) {
                continue;
            }

            try {
                return json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
            } catch (JsonException $exception) {
                throw new \RuntimeException('Unable to encode document JSON.', previous: $exception);
            }
        }

        throw new \RuntimeException("Document not found in fixture index: $relativePath.");
    }

    /** @return array{documents: list<array<string, mixed>>, metadata: array<string, mixed>} */
    private static function decodeIndex(string $json): array
    {
        try {
            $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new \RuntimeException('Unable to decode index JSON.', previous: $exception);
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException('Decoded index must be an array.');
        }

        /** @var array{documents: list<array<string, mixed>>, metadata: array<string, mixed>} $decoded */
        return $decoded;
    }
}
