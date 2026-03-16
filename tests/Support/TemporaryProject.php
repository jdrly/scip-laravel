<?php

declare(strict_types=1);

namespace Tests\Support;

use Symfony\Component\Filesystem\Filesystem;

use function microtime;
use function sprintf;
use function sys_get_temp_dir;

final class TemporaryProject
{
    public static function copyFixture(string $fixtureName): string
    {
        $filesystem = new Filesystem();
        $targetPath = sys_get_temp_dir() . '/' . sprintf(
            'scip-laravel-%s-%s',
            $fixtureName,
            md5((string) microtime(true)),
        );

        $filesystem->mirror(FixturePaths::fixture($fixtureName), $targetPath);

        return $targetPath;
    }

    public static function remove(string $path): void
    {
        (new Filesystem())->remove($path);
    }
}
