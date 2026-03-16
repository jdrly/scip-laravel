<?php

declare(strict_types=1);

namespace Tests\Unit\Project;

use PHPUnit\Framework\TestCase;
use ScipLaravel\Project\ComposerProjectReader;
use ScipLaravel\Project\ProjectFileFinder;
use Tests\Support\FixturePaths;

final class ProjectFileFinderTest extends TestCase
{
    public function testPhpFilesReturnsSortedProjectPhpFilesWithoutVendor(): void
    {
        $project = (new ComposerProjectReader())->read(FixturePaths::fixture('plain-php-modern'));
        $files = (new ProjectFileFinder())->phpFiles($project);

        $relativeFiles = array_map(
            static fn(string $path): string => substr($path, strlen($project->rootPath) + 1),
            $files,
        );

        self::assertSame([
            'src/ExampleClass.php',
            'src/HelperService.php',
            'src/functions.php',
        ], $relativeFiles);
    }
}
