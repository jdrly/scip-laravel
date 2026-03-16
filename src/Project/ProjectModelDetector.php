<?php

declare(strict_types=1);

namespace ScipLaravel\Project;

use JsonException;
use RuntimeException;
use ScipLaravel\Support\FileReader;

use function is_array;
use function is_dir;
use function is_file;
use function is_string;
use function preg_match;
use function scandir;
use function sort;

use const JSON_THROW_ON_ERROR;

final class ProjectModelDetector
{
    public function __construct(
        private readonly ComposerProjectReader $composerProjectReader = new ComposerProjectReader(),
    ) {
    }

    public function detect(string $projectRoot): ProjectModel
    {
        $composerProject = $this->composerProjectReader->read($projectRoot);
        $composerConfig = $this->readComposerJson($composerProject->rootPath);
        $laravelConstraint = $this->laravelFrameworkConstraint($composerConfig);
        $isLaravelProject = $laravelConstraint !== null;

        return new ProjectModel(
            composerProject: $composerProject,
            framework: $isLaravelProject ? 'laravel' : 'plain-php',
            laravelVersion: $isLaravelProject ? $this->detectLaravelVersion($laravelConstraint) : null,
            hasBootstrapApp: is_file($composerProject->rootPath . '/bootstrap/app.php'),
            hasBootstrapProviders: is_file($composerProject->rootPath . '/bootstrap/providers.php'),
            routeFiles: $this->routeFiles($composerProject->rootPath),
            providerFiles: $this->providerFiles($composerProject->rootPath),
            controllerDirectories: $this->directories($composerProject->rootPath, ['app/Http/Controllers']),
            modelDirectories: $this->directories($composerProject->rootPath, ['app/Models']),
            commandDirectories: $this->directories($composerProject->rootPath, ['app/Console/Commands']),
        );
    }

    /** @return array<string, mixed> */
    private function readComposerJson(string $projectRoot): array
    {
        $path = $projectRoot . '/composer.json';

        try {
            $config = json_decode(FileReader::read($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException("Invalid composer.json in project root: $projectRoot.", previous: $exception);
        }

        if (!is_array($config)) {
            throw new RuntimeException("Invalid composer.json in project root: $projectRoot.");
        }

        /** @var array<string, mixed> $normalizedConfig */
        $normalizedConfig = [];
        foreach ($config as $key => $value) {
            if (!is_string($key)) {
                throw new RuntimeException("composer.json keys must be strings in project root: $projectRoot.");
            }

            $normalizedConfig[$key] = $value;
        }

        return $normalizedConfig;
    }

    /** @param array<string, mixed> $composerConfig */
    private function laravelFrameworkConstraint(array $composerConfig): ?string
    {
        foreach (['require', 'require-dev'] as $section) {
            $dependencies = $composerConfig[$section] ?? null;
            if (!is_array($dependencies)) {
                continue;
            }

            $constraint = $dependencies['laravel/framework'] ?? null;
            if (is_string($constraint) && $constraint !== '') {
                return $constraint;
            }
        }

        return null;
    }

    private function detectLaravelVersion(string $constraint): ?string
    {
        if (preg_match('/(12|13)/', $constraint, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    /** @return list<string> */
    private function routeFiles(string $projectRoot): array
    {
        /** @var list<string> $files */
        $files = [];
        foreach (['routes/web.php', 'routes/api.php', 'routes/console.php'] as $path) {
            if (is_file($projectRoot . '/' . $path)) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /** @return list<string> */
    private function providerFiles(string $projectRoot): array
    {
        /** @var list<string> $files */
        $files = [];
        if (is_file($projectRoot . '/bootstrap/providers.php')) {
            $files[] = 'bootstrap/providers.php';
        }

        $providersDirectory = $projectRoot . '/app/Providers';
        if (!is_dir($providersDirectory)) {
            return $files;
        }

        $providerEntries = scandir($providersDirectory);
        if ($providerEntries === false) {
            return $files;
        }

        foreach ($providerEntries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $relativePath = 'app/Providers/' . $entry;
            if (is_file($projectRoot . '/' . $relativePath)) {
                $files[] = $relativePath;
            }
        }

        sort($files);

        return $files;
    }

    /**
     * @param list<string> $relativeDirectories
     * @return list<string>
     */
    private function directories(string $projectRoot, array $relativeDirectories): array
    {
        /** @var list<string> $directories */
        $directories = [];

        foreach ($relativeDirectories as $relativeDirectory) {
            if (is_dir($projectRoot . '/' . $relativeDirectory)) {
                $directories[] = $relativeDirectory;
            }
        }

        return $directories;
    }
}
