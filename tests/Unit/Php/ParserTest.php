<?php

declare(strict_types=1);

namespace Tests\Unit\Php;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ScipLaravel\Php\Parser;
use Tests\Support\FixturePaths;

final class ParserTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new Parser();
    }

    public function testParseFileThrowsForEmptySource(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Cannot parse empty PHP source.');

        $this->parser->parseFile(FixturePaths::testData('Unit/Php/testdata/empty.php'));
    }

    public function testParseFileThrowsForInvalidPhp(): void
    {
        self::expectException(RuntimeException::class);

        $this->parser->parseFile(FixturePaths::testData('Unit/Php/testdata/invalid.php'));
    }

    public function testParseFileReturnsStatementsForValidPhp(): void
    {
        $statements = $this->parser->parseFile(FixturePaths::testData('Unit/Php/testdata/valid.php'));

        self::assertCount(2, $statements);
    }
}
