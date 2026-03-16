<?php

declare(strict_types=1);

namespace ScipLaravel\Support;

use RuntimeException;

final class FileReader
{
    public static function read(string $path): string
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Cannot read file: {$path}.");
        }

        return $contents;
    }
}
