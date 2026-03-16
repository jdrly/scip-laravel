<?php

declare(strict_types=1);

namespace ScipLaravel\Project;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function sort;

final class ProjectFileFinder
{
    /** @return list<string> */
    public function phpFiles(ComposerProject $project): array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($project->rootPath));
        $files = [];
        $vendorPathSegment = DIRECTORY_SEPARATOR . trim($project->vendorDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $realPath = $file->getRealPath();
            if ($realPath === false || str_contains($realPath, $vendorPathSegment)) {
                continue;
            }

            $files[] = $realPath;
        }

        sort($files);

        return $files;
    }
}
