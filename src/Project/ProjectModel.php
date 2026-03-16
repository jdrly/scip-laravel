<?php

declare(strict_types=1);

namespace ScipLaravel\Project;

final readonly class ProjectModel
{
    /**
     * @param list<string> $routeFiles
     * @param list<string> $providerFiles
     * @param list<string> $controllerDirectories
     * @param list<string> $modelDirectories
     * @param list<string> $commandDirectories
     */
    public function __construct(
        public ComposerProject $composerProject,
        public string $framework,
        public ?string $laravelVersion,
        public bool $hasBootstrapApp,
        public bool $hasBootstrapProviders,
        public array $routeFiles,
        public array $providerFiles,
        public array $controllerDirectories,
        public array $modelDirectories,
        public array $commandDirectories,
    ) {
    }
}
