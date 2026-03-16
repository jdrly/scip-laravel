<?php

declare(strict_types=1);

namespace ScipLaravel\Config;

use JsonException;
use RuntimeException;
use ScipLaravel\Support\FileReader;

use function is_array;
use function is_string;
use function pathinfo;
use function strtolower;

use const JSON_THROW_ON_ERROR;
use const PATHINFO_EXTENSION;

final class ConfigFileLoader
{
    /** @return array<string, mixed> */
    public function load(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'json') {
            return $this->loadJson($path);
        }

        if ($extension === 'php') {
            return $this->loadPhp($path);
        }

        throw new RuntimeException("Unsupported config file extension: .$extension.");
    }

    /** @return array<string, mixed> */
    private function loadJson(string $path): array
    {
        try {
            $config = json_decode(FileReader::read($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException("Invalid JSON config file: $path.", previous: $exception);
        }

        if (!is_array($config)) {
            throw new RuntimeException("Config file must decode to an object: $path.");
        }

        return $this->normalize($config, $path);
    }

    /** @return array<string, mixed> */
    private function loadPhp(string $path): array
    {
        $config = require $path;
        if (!is_array($config)) {
            throw new RuntimeException("PHP config file must return an array: $path.");
        }

        return $this->normalize($config, $path);
    }

    /**
     * @param array<mixed, mixed> $config
     * @return array<string, mixed>
     */
    private function normalize(array $config, string $path): array
    {
        /** @var array<string, mixed> $normalized */
        $normalized = [];

        foreach ($config as $key => $value) {
            if (!is_string($key)) {
                throw new RuntimeException("Config keys must be strings: $path.");
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
