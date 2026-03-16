<?php

declare(strict_types=1);

namespace ScipLaravel\Php;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Node\Stmt;

final class AstTraverser
{
    /** @param list<Stmt> $statements */
    public function traverse(array $statements, NodeVisitor ...$visitors): void
    {
        $traverser = new NodeTraverser(
            new NameResolver(),
            new ParentConnectingVisitor(),
            ...$visitors,
        );

        $traverser->traverse($statements);
    }
}
