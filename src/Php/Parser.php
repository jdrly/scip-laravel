<?php

declare(strict_types=1);

namespace ScipLaravel\Php;

use PhpParser\Error;
use PhpParser\Node\Stmt;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use RuntimeException;
use ScipLaravel\Support\FileReader;

final readonly class Parser
{
    private PhpParser $parser;

    public function __construct()
    {
        $this->parser = new ParserFactory()->createForNewestSupportedVersion();
    }

    /** @return list<Stmt> */
    public function parse(string $code): array
    {
        if (trim($code) === '') {
            throw new RuntimeException('Cannot parse empty PHP source.');
        }

        try {
            $statements = $this->parser->parse($code);
        } catch (Error $exception) {
            throw new RuntimeException($exception->getMessage(), previous: $exception);
        }

        if ($statements === null) {
            throw new RuntimeException('Parser returned no statements.');
        }

        return array_values($statements);
    }

    /** @return list<Stmt> */
    public function parseFile(string $path): array
    {
        return $this->parse(FileReader::read($path));
    }
}
