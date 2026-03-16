<?php

declare(strict_types=1);

namespace Tests\Integration\Cli;

use PHPUnit\Framework\TestCase;
use ScipLaravel\Cli\IndexCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\FixturePaths;
use Tests\Support\TemporaryProject;

final class IndexCommandTest extends TestCase
{
    public function testExecuteWritesIndexFileForFixture(): void
    {
        $outputPath = sys_get_temp_dir() . '/scip-laravel-index-command-test.json';
        if (is_file($outputPath)) {
            unlink($outputPath);
        }

        $tester = new CommandTester(new IndexCommand());
        $exitCode = $tester->execute([
            '--project-dir' => FixturePaths::fixture('plain-php-modern'),
            '--output' => $outputPath,
            '--framework' => 'php',
            '--php-version' => '8.5',
        ]);

        self::assertSame(0, $exitCode);
        self::assertFileExists($outputPath);
        self::assertStringContainsString('"toolName": "scip-laravel"', (string) file_get_contents($outputPath));

        unlink($outputPath);
    }

    public function testCommandExposesMainOptionsAndHelpText(): void
    {
        $command = new IndexCommand();
        $definition = $command->getDefinition();
        $help = $command->getHelp();

        self::assertTrue($definition->hasOption('project-dir'));
        self::assertTrue($definition->hasOption('output'));
        self::assertTrue($definition->hasOption('framework'));
        self::assertTrue($definition->hasOption('php-version'));
        self::assertTrue($definition->hasOption('memory-limit'));
        self::assertTrue($definition->hasOption('format'));
        self::assertTrue($definition->hasOption('config'));
        self::assertStringContainsString(
            'scip-laravel index --project-dir /path/to/app --output /tmp/index.json',
            $help,
        );
        self::assertStringContainsString('CLI options override config file values.', $help);
    }

    public function testCommandWritesScipPayloadWhenRequested(): void
    {
        $outputPath = sys_get_temp_dir() . '/scip-laravel-index-command-test.scip';
        if (is_file($outputPath)) {
            unlink($outputPath);
        }

        $tester = new CommandTester(new IndexCommand());
        $exitCode = $tester->execute([
            '--project-dir' => FixturePaths::fixture('plain-php-modern'),
            '--output' => $outputPath,
            '--format' => 'scip',
            '--framework' => 'php',
            '--php-version' => '8.5',
        ]);

        self::assertSame(0, $exitCode);
        self::assertFileExists($outputPath);
        self::assertGreaterThan(0, filesize($outputPath));

        unlink($outputPath);
    }

    public function testCommandIndexesProjectCopiedOutsideRepository(): void
    {
        $temporaryProjectPath = TemporaryProject::copyFixture('plain-php-modern');
        $outputPath = sys_get_temp_dir() . '/scip-laravel-index-command-external-test.json';
        if (is_file($outputPath)) {
            unlink($outputPath);
        }

        try {
            $tester = new CommandTester(new IndexCommand());
            $exitCode = $tester->execute([
                '--project-dir' => $temporaryProjectPath,
                '--output' => $outputPath,
                '--framework' => 'php',
                '--php-version' => '8.4',
            ]);

            self::assertSame(0, $exitCode);
            self::assertFileExists($outputPath);
            $resolvedProjectPath = realpath($temporaryProjectPath);
            $normalizedProjectPath = $resolvedProjectPath === false
                ? $temporaryProjectPath
                : $resolvedProjectPath;
            self::assertStringContainsString(
                '"projectRoot": "file://' . $normalizedProjectPath . '"',
                (string) file_get_contents($outputPath),
            );
        } finally {
            if (is_file($outputPath)) {
                unlink($outputPath);
            }
            TemporaryProject::remove($temporaryProjectPath);
        }
    }
}
