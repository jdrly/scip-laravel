<?php

declare(strict_types=1);

namespace Tests\Unit\Php;

use PHPUnit\Framework\TestCase;
use ScipLaravel\Php\DocBlockParser;

final class DocBlockParserTest extends TestCase
{
    private DocBlockParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new DocBlockParser();
    }

    public function testParseReturnTypeReadsGenericReturnType(): void
    {
        $type = $this->parser->parseReturnType('/** @return array<string, int> */');

        self::assertNotNull($type);
        self::assertSame('array<string, int>', (string) $type);
    }

    public function testParsePropertyTagsReadsPropertyVariants(): void
    {
        $properties = $this->parser->parsePropertyTags(
            <<<'PHPDOC'
/**
 * @property string $name
 * @property-read int $id
 * @property-write bool $active
 */
PHPDOC,
        );

        self::assertCount(3, $properties);
        self::assertSame('$name', $properties[0]->propertyName);
        self::assertSame('$id', $properties[1]->propertyName);
        self::assertSame('$active', $properties[2]->propertyName);
    }

    public function testParseMethodTagsReadsMethodDefinitions(): void
    {
        $methods = $this->parser->parseMethodTags(
            <<<'PHPDOC'
/**
 * @method string fullName()
 * @method static self make(string $name)
 */
PHPDOC,
        );

        self::assertCount(2, $methods);
        self::assertSame('fullName', $methods[0]->methodName);
        self::assertFalse($methods[0]->isStatic);
        self::assertSame('make', $methods[1]->methodName);
        self::assertTrue($methods[1]->isStatic);
    }
}
