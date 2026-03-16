<?php

declare(strict_types=1);

namespace ScipLaravel\Laravel;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\PosResolver;
use ScipLaravel\Project\ProjectModel;
use ScipLaravel\Scip\Occurrence;
use ScipLaravel\Scip\SymbolNamer;
use ScipLaravel\Support\FileReader;

use function in_array;
use function str_starts_with;

final class EloquentAnalyzer
{
    private const array RELATION_METHODS = [
        'hasOne',
        'hasMany',
        'belongsTo',
        'belongsToMany',
        'morphTo',
        'morphOne',
        'morphMany',
    ];

    public function __construct(
        private readonly AstTraverser $traverser,
        private readonly SymbolNamer $symbolNamer,
        private readonly ProjectModel $projectModel,
        private readonly string $projectVersion,
    ) {
    }

    /**
     * @param list<Stmt> $statements
     * @return list<Occurrence>
     */
    public function occurrences(string $absolutePath, string $relativePath, array $statements): array
    {
        if (!str_starts_with($relativePath, 'app/Models/')) {
            return [];
        }

        $positionResolver = new PosResolver(FileReader::read($absolutePath));
        $visitor = new class ($this, $positionResolver) extends NodeVisitorAbstract
        {
            /** @var list<Occurrence> */
            public array $occurrences = [];

            public function __construct(
                private readonly EloquentAnalyzer $analyzer,
                private readonly PosResolver $positionResolver,
            ) {
            }

            public function enterNode(Node $node): ?Node
            {
                foreach ($this->analyzer->analyzeNode($node, $this->positionResolver) as $occurrence) {
                    $this->occurrences[] = $occurrence;
                }

                return null;
            }
        };

        $this->traverser->traverse($statements, $visitor);

        return $visitor->occurrences;
    }

    /** @return list<Occurrence> */
    public function analyzeNode(Node $node, PosResolver $positionResolver): array
    {
        if (!$node instanceof ClassMethod) {
            return [];
        }

        foreach ($node->stmts ?? [] as $statement) {
            if (!$statement instanceof Return_ || !$statement->expr instanceof MethodCall) {
                continue;
            }

            $methodName = $statement->expr->name;
            if (
                !$methodName instanceof Identifier
                || !in_array($methodName->toString(), self::RELATION_METHODS, true)
            ) {
                continue;
            }

            $relatedModelArgument = $statement->expr->args[0]->value ?? null;
            if (!$relatedModelArgument instanceof ClassConstFetch) {
                continue;
            }

            $className = $this->className($relatedModelArgument);
            if ($className === null) {
                continue;
            }

            return [new Occurrence(
                range: $positionResolver->range($relatedModelArgument->class),
                symbol: $this->symbolNamer->classLike(
                    $this->projectModel->composerProject->packageName,
                    $this->projectVersion,
                    $className,
                ),
                role: 'reference',
                syntaxKind: 'type',
            )];
        }

        return [];
    }

    private function className(ClassConstFetch $classConstFetch): ?string
    {
        if (!$classConstFetch->class instanceof Name) {
            return null;
        }

        $resolvedName = $classConstFetch->class->getAttribute('resolvedName');
        if ($resolvedName instanceof Name) {
            return $resolvedName->toString();
        }

        $namespacedName = $classConstFetch->class->getAttribute('namespacedName');
        if ($namespacedName instanceof Name) {
            return $namespacedName->toString();
        }

        return $classConstFetch->class->toString();
    }
}
