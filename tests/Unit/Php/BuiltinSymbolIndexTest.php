<?php

declare(strict_types=1);

namespace Tests\Unit\Php;

use PHPUnit\Framework\TestCase;
use ScipLaravel\Php\BuiltinSymbolIndex;

final class BuiltinSymbolIndexTest extends TestCase
{
    private BuiltinSymbolIndex $index;

    protected function setUp(): void
    {
        parent::setUp();

        $this->index = new BuiltinSymbolIndex();
    }

    public function testRecognizesBuiltinClassLikeSymbols(): void
    {
        self::assertTrue($this->index->isClassLike('Exception'));
        self::assertTrue($this->index->isClassLike('Dom\\HTMLDocument'));
        self::assertTrue($this->index->isClassLike('RoundingMode'));
    }

    public function testRecognizesBuiltinFunctionsAndConstants(): void
    {
        self::assertTrue($this->index->isFunction('strlen'));
        self::assertTrue($this->index->isFunction('request_parse_body'));
        self::assertTrue($this->index->isConstant('PHP_VERSION'));
    }

    public function testStubPathForReturnsExistingStubFile(): void
    {
        $functionStubPath = $this->index->stubPathFor('request_parse_body');
        $classStubPath = $this->index->stubPathFor('Dom\\HTMLDocument');
        $enumStubPath = $this->index->stubPathFor('RoundingMode');

        self::assertNotNull($functionStubPath);
        self::assertNotNull($classStubPath);
        self::assertNotNull($enumStubPath);
        self::assertFileExists($functionStubPath);
        self::assertFileExists($classStubPath);
        self::assertFileExists($enumStubPath);
    }
}
