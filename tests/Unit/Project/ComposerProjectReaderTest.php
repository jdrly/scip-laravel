<?php

declare(strict_types=1);

namespace Tests\Unit\Project;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ScipLaravel\Project\ComposerProjectReader;
use Tests\Support\FixturePaths;

final class ComposerProjectReaderTest extends TestCase
{
    private ComposerProjectReader $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reader = new ComposerProjectReader();
    }

    public function testReadUsesDefaultVendorDirectory(): void
    {
        $project = $this->reader->read(FixturePaths::testData('Unit/Project/testdata/default'));

        self::assertSame('tests/default-project', $project->packageName);
        self::assertSame('vendor', $project->vendorDir);
        self::assertTrue($project->hasComposerLock);
    }

    public function testReadUsesConfiguredVendorDirectory(): void
    {
        $project = $this->reader->read(FixturePaths::testData('Unit/Project/testdata/custom-vendor'));

        self::assertSame('tests/custom-vendor-project', $project->packageName);
        self::assertSame('tools/vendor', $project->vendorDir);
        self::assertFalse($project->hasComposerLock);
    }

    public function testReadThrowsWhenComposerJsonIsMissing(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Missing composer.json');

        $this->reader->read(FixturePaths::testData('Unit/Project/testdata/missing'));
    }

    public function testReadThrowsWhenComposerJsonIsInvalid(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Invalid composer.json');

        $this->reader->read(FixturePaths::testData('Unit/Project/testdata/invalid-json'));
    }
}
