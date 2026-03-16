<?php

declare(strict_types=1);

namespace ScipLaravel\Project;

use JsonException;
use RuntimeException;
use ScipLaravel\Support\FileReader;
use Webmozart\Assert\Assert;

use function realpath;

final class ComposerProjectReader
{
    public function read(string $rootPath): ComposerProject
    {
        $resolvedRootPath = realpath(rtrim($rootPath, DIRECTORY_SEPARATOR));
        $normalizedRootPath = $resolvedRootPath === false
            ? rtrim($rootPath, DIRECTORY_SEPARATOR)
            : $resolvedRootPath;
        $composerJsonPath = $normalizedRootPath . DIRECTORY_SEPARATOR . 'composer.json';
        $composerLockPath = $normalizedRootPath . DIRECTORY_SEPARATOR . 'composer.lock';

        if (!is_file($composerJsonPath)) {
            throw new RuntimeException("Missing composer.json in project root: $normalizedRootPath.");
        }

        try {
            $data = json_decode(FileReader::read($composerJsonPath), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                "Invalid composer.json in project root: $normalizedRootPath.",
                previous: $exception,
            );
        }

        if (!is_array($data)) {
            throw new RuntimeException("Invalid composer.json in project root: $normalizedRootPath.");
        }

        $packageName = $data['name'] ?? null;
        Assert::stringNotEmpty($packageName, 'composer.json must define a non-empty package name.');

        $vendorDir = 'vendor';
        if (is_array($data['config'] ?? null) && is_string($data['config']['vendor-dir'] ?? null)) {
            $configuredVendorDir = trim($data['config']['vendor-dir'], '/');
            if ($configuredVendorDir !== '') {
                $vendorDir = $configuredVendorDir;
            }
        }

        return new ComposerProject(
            rootPath: $normalizedRootPath,
            packageName: $packageName,
            vendorDir: $vendorDir,
            hasComposerLock: is_file($composerLockPath),
        );
    }
}
