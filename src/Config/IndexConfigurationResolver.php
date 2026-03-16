<?php

declare(strict_types=1);

namespace ScipLaravel\Config;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;

use function implode;
use function in_array;
use function is_string;
use function realpath;
use function trim;

final class IndexConfigurationResolver
{
    private const array FRAMEWORKS = ['auto', 'php', 'laravel'];

    private const array PHP_VERSIONS = ['auto', '8.4', '8.5'];

    public function __construct(private readonly ConfigFileLoader $configFileLoader = new ConfigFileLoader())
    {
    }

    public function resolve(InputInterface $input): IndexConfiguration
    {
        $fileConfig = [];
        $configPath = $this->stringOption($input, 'config');
        if ($configPath !== null) {
            $fileConfig = $this->configFileLoader->load($configPath);
        }

        $projectDir = $this->requiredString(
            $this->stringOption($input, 'project-dir') ?? $this->stringConfig($fileConfig, 'projectDir'),
            'project-dir',
        );
        $outputPath = $this->requiredString(
            $this->stringOption($input, 'output') ?? $this->stringConfig($fileConfig, 'output'),
            'output',
        );
        $framework = $this->validatedValue(
            $this->stringOption($input, 'framework') ?? $this->stringConfig($fileConfig, 'framework') ?? 'auto',
            self::FRAMEWORKS,
            'framework',
        );
        $phpVersion = $this->validatedValue(
            $this->stringOption($input, 'php-version') ?? $this->stringConfig($fileConfig, 'phpVersion') ?? 'auto',
            self::PHP_VERSIONS,
            'php-version',
        );
        $memoryLimit = $this->requiredString(
            $this->stringOption($input, 'memory-limit') ?? $this->stringConfig($fileConfig, 'memoryLimit') ?? '1G',
            'memory-limit',
        );

        $normalizedProjectDir = realpath($projectDir);
        if ($normalizedProjectDir === false) {
            throw new RuntimeException("Project directory does not exist: $projectDir.");
        }

        return new IndexConfiguration(
            projectDir: $normalizedProjectDir,
            outputPath: $outputPath,
            framework: $framework,
            phpVersion: $phpVersion,
            memoryLimit: $memoryLimit,
        );
    }

    private function stringOption(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new RuntimeException("Option '$name' must be a string.");
        }

        $trimmedValue = trim($value);
        return $trimmedValue === '' ? null : $trimmedValue;
    }

    /** @param array<string, mixed> $config */
    private function stringConfig(array $config, string $name): ?string
    {
        $value = $config[$name] ?? null;
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new RuntimeException("Config value '$name' must be a string.");
        }

        $trimmedValue = trim($value);
        return $trimmedValue === '' ? null : $trimmedValue;
    }

    /** @param list<string> $allowedValues */
    private function validatedValue(string $value, array $allowedValues, string $name): string
    {
        if (!in_array($value, $allowedValues, true)) {
            $values = implode(', ', $allowedValues);
            throw new RuntimeException("Invalid value for '$name'. Expected one of: $values.");
        }

        return $value;
    }

    private function requiredString(?string $value, string $name): string
    {
        if ($value === null) {
            throw new RuntimeException("Missing required option: $name.");
        }

        return $value;
    }
}
