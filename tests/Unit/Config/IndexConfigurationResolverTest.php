<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ScipLaravel\Cli\IndexCommand;
use ScipLaravel\Config\IndexConfigurationResolver;
use Symfony\Component\Console\Input\ArrayInput;
use Tests\Support\FixturePaths;

final class IndexConfigurationResolverTest extends TestCase
{
    private IndexConfigurationResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new IndexConfigurationResolver();
    }

    public function testResolveUsesDirectCliValues(): void
    {
        $configuration = $this->resolver->resolve($this->createInput([
            '--project-dir' => FixturePaths::fixture('plain-php-modern'),
            '--output' => '/tmp/index.json',
            '--framework' => 'php',
            '--php-version' => '8.4',
            '--memory-limit' => '768M',
        ]));

        self::assertSame(FixturePaths::fixture('plain-php-modern'), $configuration->projectDir);
        self::assertSame('/tmp/index.json', $configuration->outputPath);
        self::assertSame('php', $configuration->framework);
        self::assertSame('8.4', $configuration->phpVersion);
        self::assertSame('768M', $configuration->memoryLimit);
    }

    public function testResolveUsesConfigFileValues(): void
    {
        $projectDir = FixturePaths::fixture('plain-php-modern');
        $configPath = tempnam(sys_get_temp_dir(), 'scip-laravel-config-');
        self::assertNotFalse($configPath);
        $jsonConfigPath = $configPath . '.json';
        rename($configPath, $jsonConfigPath);

        file_put_contents($jsonConfigPath, json_encode([
            'projectDir' => $projectDir,
            'output' => '/tmp/config-index.json',
            'framework' => 'laravel',
            'phpVersion' => '8.5',
            'memoryLimit' => '512M',
        ], JSON_THROW_ON_ERROR));

        try {
            $configuration = $this->resolver->resolve($this->createInput([
                '--config' => $jsonConfigPath,
            ]));
        } finally {
            unlink($jsonConfigPath);
        }

        self::assertSame($projectDir, $configuration->projectDir);
        self::assertSame('/tmp/config-index.json', $configuration->outputPath);
        self::assertSame('laravel', $configuration->framework);
        self::assertSame('8.5', $configuration->phpVersion);
        self::assertSame('512M', $configuration->memoryLimit);
    }

    public function testResolveLetsCliOverrideConfigFileValues(): void
    {
        $projectDir = FixturePaths::fixture('plain-php-modern');
        $configPath = tempnam(sys_get_temp_dir(), 'scip-laravel-config-');
        self::assertNotFalse($configPath);
        $jsonConfigPath = $configPath . '.json';
        rename($configPath, $jsonConfigPath);

        file_put_contents($jsonConfigPath, json_encode([
            'projectDir' => $projectDir,
            'output' => '/tmp/config-index.json',
            'framework' => 'laravel',
            'phpVersion' => '8.5',
            'memoryLimit' => '512M',
        ], JSON_THROW_ON_ERROR));

        try {
            $configuration = $this->resolver->resolve($this->createInput([
                '--config' => $jsonConfigPath,
                '--output' => '/tmp/cli-index.json',
                '--framework' => 'php',
            ]));
        } finally {
            unlink($jsonConfigPath);
        }

        self::assertSame('/tmp/cli-index.json', $configuration->outputPath);
        self::assertSame('php', $configuration->framework);
        self::assertSame('8.5', $configuration->phpVersion);
    }

    public function testResolveThrowsForMissingRequiredOptions(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Missing required option: project-dir');

        $this->resolver->resolve($this->createInput([
            '--output' => '/tmp/index.json',
        ]));
    }

    public function testResolveThrowsForInvalidFramework(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage("Invalid value for 'framework'");

        $this->resolver->resolve($this->createInput([
            '--project-dir' => FixturePaths::fixture('plain-php-modern'),
            '--output' => '/tmp/index.json',
            '--framework' => 'symfony',
        ]));
    }

    /** @param array<string, string> $parameters */
    private function createInput(array $parameters): ArrayInput
    {
        return new ArrayInput($parameters, (new IndexCommand())->getDefinition());
    }
}
