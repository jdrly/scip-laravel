<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ScipLaravel\Config\ConfigFileLoader;
use Tests\Support\FixturePaths;

final class ConfigFileLoaderTest extends TestCase
{
    private ConfigFileLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new ConfigFileLoader();
    }

    public function testLoadReadsJsonConfig(): void
    {
        $config = $this->loader->load(FixturePaths::testData('Unit/Config/testdata/config.json'));

        self::assertSame('/tmp/project-from-config', $config['projectDir']);
        self::assertSame('/tmp/output-from-config.json', $config['output']);
    }

    public function testLoadReadsPhpConfig(): void
    {
        $config = $this->loader->load(FixturePaths::testData('Unit/Config/testdata/config.php'));

        self::assertSame('/tmp/project-from-php-config', $config['projectDir']);
        self::assertSame('256M', $config['memoryLimit']);
    }

    public function testLoadThrowsForInvalidJson(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Invalid JSON config file');

        $this->loader->load(FixturePaths::testData('Unit/Config/testdata/invalid.json'));
    }

    public function testLoadThrowsForUnsupportedExtension(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Unsupported config file extension');

        $this->loader->load(FixturePaths::testData('Unit/Config/testdata/config.txt'));
    }
}
