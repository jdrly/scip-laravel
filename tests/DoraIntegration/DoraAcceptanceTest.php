<?php

declare(strict_types=1);

namespace Tests\DoraIntegration;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixturePaths;
use Tests\Support\TemporaryProject;

use function escapeshellarg;
use function file_put_contents;
use function implode;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;
use function mkdir;
use function shell_exec;
use function sprintf;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

final class DoraAcceptanceTest extends TestCase
{
    private string $projectPath;

    protected function setUp(): void
    {
        parent::setUp();

        if (shell_exec('command -v dora') === null) {
            self::markTestSkipped('dora CLI is not available.');
        }

        $this->projectPath = TemporaryProject::copyFixture('laravel12-app');
        $this->runCommand(sprintf('cd %s && git init -q', escapeshellarg($this->projectPath)));
        mkdir($this->projectPath . '/.dora');

        $repoRoot = FixturePaths::projectRoot();
        $config = [
            'root' => $this->projectPath,
            'scip' => '.dora/index.scip',
            'db' => '.dora/dora.db',
            'language' => 'php',
            'commands' => [
                'index' => sprintf(
                    'php %s/bin/scip-laravel index --project-dir . --output .dora/index.scip ' .
                    '--format scip --framework laravel --php-version 8.5',
                    $repoRoot,
                ),
            ],
            'lastIndexed' => null,
        ];

        file_put_contents(
            $this->projectPath . '/.dora/config.json',
            json_encode($config, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }

    protected function tearDown(): void
    {
        TemporaryProject::remove($this->projectPath);

        parent::tearDown();
    }

    public function testDoraIndexesLaravelFixtureAndFindsSymbols(): void
    {
        $this->runInProject('dora index --full');

        $status = $this->jsonInProject('dora --json status');
        self::assertTrue($this->boolValue($status, 'initialized'));
        self::assertTrue($this->boolValue($status, 'indexed'));
        self::assertGreaterThan(0, $this->intValue($status, 'file_count'));
        self::assertGreaterThan(0, $this->intValue($status, 'symbol_count'));

        $symbolResults = $this->jsonInProject('dora --json symbol HomeController');
        $results = $this->arrayOfArraysValue($symbolResults, 'results');
        self::assertNotEmpty($results);
        self::assertSame('app/Http/Controllers/HomeController.php', $this->stringValue($results[0], 'path'));
    }

    public function testDoraFindsRouteAndEloquentReferences(): void
    {
        $this->runInProject('dora index --full');

        $aboutReferences = $this->jsonInProject('dora --json refs about');
        $aboutResults = $this->arrayOfArraysValue($aboutReferences, 'results');
        self::assertNotEmpty($aboutResults);
        self::assertContains('routes/web.php', $this->stringListValue($aboutResults[0], 'references'));

        $invokeReferences = $this->jsonInProject('dora --json refs __invoke');
        $invokeResults = $this->arrayOfArraysValue($invokeReferences, 'results');
        self::assertNotEmpty($invokeResults);
        self::assertContains('routes/web.php', $this->stringListValue($invokeResults[0], 'references'));

        $postReferences = $this->jsonInProject('dora --json refs Post');
        $postResults = $this->arrayOfArraysValue($postReferences, 'results');
        self::assertNotEmpty($postResults);
        $references = $this->stringListValue($postResults[0], 'references');
        self::assertContains('app/Models/User.php', $references);
        self::assertContains('app/Providers/AppServiceProvider.php', $references);
    }

    private function runInProject(string $command): void
    {
        $this->runCommand(sprintf('cd %s && %s', escapeshellarg($this->projectPath), $command));
    }

    /** @return array<string, mixed> */
    private function jsonInProject(string $command): array
    {
        $output = $this->runCommand(sprintf('cd %s && %s', escapeshellarg($this->projectPath), $command));
        $decoded = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private function runCommand(string $command): string
    {
        $output = [];
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);
        $outputText = implode("\n", $output);

        self::assertSame(0, $exitCode, $outputText);

        return $outputText;
    }

    /** @param array<string, mixed> $values */
    private function boolValue(array $values, string $key): bool
    {
        self::assertArrayHasKey($key, $values);
        self::assertTrue(is_bool($values[$key]));

        return $values[$key];
    }

    /** @param array<string, mixed> $values */
    private function intValue(array $values, string $key): int
    {
        self::assertArrayHasKey($key, $values);
        self::assertTrue(is_int($values[$key]));

        return $values[$key];
    }

    /** @param array<string, mixed> $values */
    private function stringValue(array $values, string $key): string
    {
        self::assertArrayHasKey($key, $values);
        self::assertTrue(is_string($values[$key]));

        return $values[$key];
    }

    /** @param array<string, mixed> $values @return list<array<string, mixed>> */
    private function arrayOfArraysValue(array $values, string $key): array
    {
        self::assertArrayHasKey($key, $values);
        self::assertTrue(is_array($values[$key]));

        $items = [];
        foreach ($values[$key] as $item) {
            self::assertTrue(is_array($item));
            $items[] = $item;
        }

        return $items;
    }

    /** @param array<string, mixed> $values @return list<string> */
    private function stringListValue(array $values, string $key): array
    {
        self::assertArrayHasKey($key, $values);
        self::assertTrue(is_array($values[$key]));

        $items = [];
        foreach ($values[$key] as $item) {
            self::assertTrue(is_string($item));
            $items[] = $item;
        }

        return $items;
    }
}
