<?php

declare(strict_types=1);

namespace ScipLaravel\Laravel;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\PosResolver;
use ScipLaravel\Scip\Occurrence;
use ScipLaravel\Support\FileReader;

use function in_array;
use function str_starts_with;

final class ConsoleAnalyzer
{
    public function __construct(
        private readonly AstTraverser $traverser,
        private readonly CommandRegistry $commandRegistry,
    ) {
    }

    /**
     * @param list<Stmt> $statements
     * @return list<Occurrence>
     */
    public function occurrences(string $absolutePath, string $relativePath, array $statements): array
    {
        $positionResolver = new PosResolver(FileReader::read($absolutePath));
        $visitor = new class ($this, $relativePath, $positionResolver) extends NodeVisitorAbstract
        {
            /** @var list<Occurrence> */
            public array $occurrences = [];

            public function __construct(
                private readonly ConsoleAnalyzer $analyzer,
                private readonly string $relativePath,
                private readonly PosResolver $positionResolver,
            ) {
            }

            public function enterNode(Node $node): ?Node
            {
                foreach (
                    $this->analyzer->analyzeNode($node, $this->relativePath, $this->positionResolver) as $occurrence
                ) {
                    $this->occurrences[] = $occurrence;
                }

                return null;
            }
        };

        $this->traverser->traverse($statements, $visitor);

        return $visitor->occurrences;
    }

    /** @return list<Occurrence> */
    public function analyzeNode(Node $node, string $relativePath, PosResolver $positionResolver): array
    {
        if ($relativePath === 'routes/console.php') {
            return $this->routeConsoleOccurrences($node, $positionResolver);
        }

        if (!str_starts_with($relativePath, 'app/Console/Commands/')) {
            return [];
        }

        if (!$node instanceof Property || !$node->props[0]->default instanceof String_) {
            return [];
        }

        $propertyName = $node->props[0]->name->toString();
        if (!in_array($propertyName, ['signature', 'name'], true)) {
            return [];
        }

        $definitions = $this->commandRegistry->forSourcePath($relativePath);
        if ($definitions === []) {
            return [];
        }

        return [new Occurrence(
            range: $positionResolver->range($node->props[0]->default),
            symbol: $definitions[0]->classSymbol,
            role: 'reference',
            syntaxKind: 'string',
        )];
    }

    /** @return list<Occurrence> */
    private function routeConsoleOccurrences(Node $node, PosResolver $positionResolver): array
    {
        if (!$node instanceof StaticCall || !$node->class instanceof Name || !$node->name instanceof Identifier) {
            return [];
        }

        if ($node->name->toString() !== 'command') {
            return [];
        }

        $resolvedName = $node->class->getAttribute('resolvedName');
        $className = $resolvedName instanceof Name ? $resolvedName->toString() : $node->class->toString();
        if (!in_array($className, ['Artisan', 'Illuminate\\Support\\Facades\\Artisan'], true)) {
            return [];
        }

        $signatureArgument = $node->args[0]->value ?? null;
        if (!$signatureArgument instanceof String_) {
            return [];
        }

        $command = $this->commandRegistry->findBySignature($signatureArgument->value);
        if ($command === null) {
            return [];
        }

        return [new Occurrence(
            range: $positionResolver->range($signatureArgument),
            symbol: $command->classSymbol,
            role: 'reference',
            syntaxKind: 'string',
        )];
    }
}
