<?php

declare(strict_types=1);

namespace ScipLaravel\Php;

use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

use function array_values;

final readonly class DocBlockParser
{
    private PhpDocParser $parser;

    private Lexer $lexer;

    public function __construct()
    {
        $config = new ParserConfig(usedAttributes: ['lines' => true, 'indexes' => true]);
        $constExprParser = new ConstExprParser($config);
        $typeParser = new TypeParser($config, $constExprParser);

        $this->parser = new PhpDocParser($config, $typeParser, $constExprParser);
        $this->lexer = new Lexer($config);
    }

    public function parse(string $docBlock): PhpDocNode
    {
        $iterator = new TokenIterator($this->lexer->tokenize($docBlock));

        return $this->parser->parse($iterator);
    }

    public function parseReturnType(string $docBlock): ?TypeNode
    {
        $tags = $this->parse($docBlock)->getReturnTagValues();

        return $tags[0]->type ?? null;
    }

    /** @return list<PropertyTagValueNode> */
    public function parsePropertyTags(string $docBlock): array
    {
        $node = $this->parse($docBlock);

        return array_values([
            ...$node->getPropertyTagValues(),
            ...$node->getPropertyReadTagValues(),
            ...$node->getPropertyWriteTagValues(),
        ]);
    }

    /** @return list<MethodTagValueNode> */
    public function parseMethodTags(string $docBlock): array
    {
        return array_values($this->parse($docBlock)->getMethodTagValues());
    }
}
