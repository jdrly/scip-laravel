<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use ScipLaravel\Support\ToolRuntimePaths;
use Tests\Support\FixturePaths;

final class ToolRuntimePathsTest extends TestCase
{
    public function testPackageRootPointsAtCurrentRepository(): void
    {
        $paths = new ToolRuntimePaths();

        self::assertSame(FixturePaths::projectRoot(), $paths->packageRoot());
        self::assertFileExists($paths->packageRoot() . '/composer.json');
    }

    public function testPhpStormStubsRootPointsAtInstalledToolDependency(): void
    {
        $paths = new ToolRuntimePaths();
        $stubsRoot = $paths->phpStormStubsRoot();

        self::assertDirectoryExists($stubsRoot);
        self::assertFileExists($stubsRoot . '/PhpStormStubsMap.php');
    }
}
