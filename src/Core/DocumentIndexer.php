<?php

declare(strict_types=1);

namespace ScipLaravel\Core;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\PosResolver;
use ScipLaravel\Php\TypeResolver;
use ScipLaravel\Project\ComposerProject;
use ScipLaravel\Scip\Document;
use ScipLaravel\Scip\Occurrence;
use ScipLaravel\Scip\SymbolInformation;
use ScipLaravel\Scip\SymbolNamer;
use ScipLaravel\Support\FileReader;

use function is_string;

final class DocumentIndexer
{
    private PosResolver $positionResolver;

    /** @var list<SymbolInformation> */
    private array $symbols = [];

    /** @var list<Occurrence> */
    private array $occurrences = [];

    public function __construct(
        private readonly AstTraverser $traverser,
        private readonly SymbolNamer $symbolNamer,
        private readonly TypeResolver $typeResolver,
        private readonly ComposerProject $project,
        private readonly string $projectVersion,
    ) {
    }

    /** @param list<Stmt> $statements */
    public function index(string $absolutePath, string $relativePath, array $statements): Document
    {
        $this->positionResolver = new PosResolver(FileReader::read($absolutePath));
        $this->symbols = [];
        $this->occurrences = [];

        $this->traverser->traverse($statements, new class ($this) extends NodeVisitorAbstract
        {
            public function __construct(private readonly DocumentIndexer $indexer)
            {
            }

            public function enterNode(Node $node): ?Node
            {
                $this->indexer->enterNode($node);
                return null;
            }

            public function leaveNode(Node $node): ?Node
            {
                $this->indexer->leaveNode($node);
                return null;
            }
        });

        return new Document(
            relativePath: $relativePath,
            occurrences: $this->occurrences,
            symbols: $this->symbols,
        );
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Function_ || $node instanceof ClassMethod) {
            $this->typeResolver->enterScope();
        }

        if ($node instanceof ClassLike && $node->name !== null) {
            $this->define(
                $this->symbolNamer->classLike(
                    $this->project->packageName,
                    $this->projectVersion,
                    $this->resolvedClassName($node),
                ),
                'class',
                $node->name,
                'type',
            );
            $this->indexClassLikeRelations($node);
            return;
        }

        if ($node instanceof ClassMethod) {
            $className = $this->currentClassName($node);
            if ($className === null) {
                return;
            }

            $this->define(
                $this->symbolNamer->method(
                    $this->project->packageName,
                    $this->projectVersion,
                    $className,
                    $node->name->toString(),
                ),
                'method',
                $node->name,
                'method',
            );
            $this->indexParameters($node->params);
            $this->indexTypeNode($node->returnType);
            return;
        }

        if ($node instanceof Function_) {
            $this->define(
                $this->symbolNamer->function(
                    $this->project->packageName,
                    $this->projectVersion,
                    $this->resolvedFunctionName($node),
                ),
                'function',
                $node->name,
                'function',
            );
            $this->indexParameters($node->params);
            $this->indexTypeNode($node->returnType);
            return;
        }

        if ($node instanceof Property) {
            $this->indexTypeNode($node->type);
            return;
        }

        if ($node instanceof PropertyItem) {
            $className = $this->currentClassName($node);
            if ($className === null) {
                return;
            }

            $this->define(
                $this->symbolNamer->property(
                    $this->project->packageName,
                    $this->projectVersion,
                    $className,
                    $node->name->toString(),
                ),
                'property',
                $node->name,
                'property',
            );
            return;
        }

        if ($node instanceof Assign && $node->var instanceof Variable && is_string($node->var->name)) {
            $className = $this->typeResolver->resolveExpressionClassName(
                $node->expr,
                $this->currentClassName($node),
            );
            if ($className !== null) {
                $this->typeResolver->rememberVariableType($node->var->name, $className);
            }
            return;
        }

        if ($node instanceof New_ && $node->class instanceof Name) {
            $this->referenceClass($node->class, $node->class);
            return;
        }

        if ($node instanceof StaticCall && $node->class instanceof Name) {
            $this->referenceClass($node->class, $node->class);
            if ($node->name instanceof Identifier) {
                $this->reference(
                    $this->symbolNamer->method(
                        $this->project->packageName,
                        $this->projectVersion,
                        $this->typeResolver->resolveName($node->class),
                        $node->name->toString(),
                    ),
                    $node->name,
                    'method',
                );
            }
            return;
        }

        if ($node instanceof MethodCall && $node->name instanceof Identifier) {
            $className = $this->typeResolver->resolveExpressionClassName(
                $node->var,
                $this->currentClassName($node),
            );
            if ($className === null) {
                return;
            }

            $this->reference(
                $this->symbolNamer->method(
                    $this->project->packageName,
                    $this->projectVersion,
                    $className,
                    $node->name->toString(),
                ),
                $node->name,
                'method',
            );
            return;
        }

        if ($node instanceof PropertyFetch && $node->name instanceof Identifier) {
            $className = $this->typeResolver->resolveExpressionClassName(
                $node->var,
                $this->currentClassName($node),
            );
            if ($className === null) {
                return;
            }

            $this->reference(
                $this->symbolNamer->property(
                    $this->project->packageName,
                    $this->projectVersion,
                    $className,
                    $node->name->toString(),
                ),
                $node->name,
                'property',
            );
        }
    }

    public function leaveNode(Node $node): void
    {
        if ($node instanceof Function_ || $node instanceof ClassMethod) {
            $this->typeResolver->leaveScope();
        }
    }

    private function indexClassLikeRelations(ClassLike $node): void
    {
        if ($node instanceof Stmt\Class_ && $node->extends instanceof Name) {
            $this->referenceClass($node->extends, $node->extends);
        }

        foreach ($node->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $traitName) {
                $this->referenceClass($traitName, $traitName);
            }
        }

        if ($node instanceof Stmt\Class_) {
            foreach ($node->implements as $interfaceName) {
                $this->referenceClass($interfaceName, $interfaceName);
            }
        }

        if ($node instanceof Stmt\Interface_) {
            foreach ($node->extends as $extendedInterface) {
                $this->referenceClass($extendedInterface, $extendedInterface);
            }
        }
    }

    /** @param array<array-key, Param> $parameters */
    private function indexParameters(array $parameters): void
    {
        foreach ($parameters as $parameter) {
            $this->indexTypeNode($parameter->type);
        }
    }

    private function indexTypeNode(Node|null $type): void
    {
        if ($type instanceof NullableType) {
            $this->indexTypeNode($type->type);
            return;
        }

        if ($type instanceof ComplexType || $type instanceof Identifier || $type === null) {
            return;
        }

        if ($type instanceof Name) {
            $this->referenceClass($type, $type);
        }
    }

    private function referenceClass(Name $name, Node $node): void
    {
        $this->reference(
            $this->symbolNamer->classLike(
                $this->project->packageName,
                $this->projectVersion,
                $this->typeResolver->resolveName($name),
            ),
            $node,
            'type',
        );
    }

    private function define(string $symbol, string $kind, Node $node, string $syntaxKind): void
    {
        $this->symbols[] = new SymbolInformation($symbol, $kind);
        $this->occurrences[] = new Occurrence(
            range: $this->positionResolver->range($node),
            symbol: $symbol,
            role: 'definition',
            syntaxKind: $syntaxKind,
        );
    }

    private function reference(string $symbol, Node $node, string $syntaxKind): void
    {
        $this->occurrences[] = new Occurrence(
            range: $this->positionResolver->range($node),
            symbol: $symbol,
            role: 'reference',
            syntaxKind: $syntaxKind,
        );
    }

    private function currentClassName(Node $node): ?string
    {
        while (($node = $node->getAttribute('parent')) instanceof Node) {
            if ($node instanceof ClassLike && $node->name !== null) {
                return $this->resolvedClassName($node);
            }
        }

        return null;
    }

    private function resolvedClassName(ClassLike $node): string
    {
        return $node->namespacedName?->toString() ?? $node->name?->toString() ?? 'anonymous';
    }

    private function resolvedFunctionName(Function_ $node): string
    {
        return $node->namespacedName?->toString() ?? $node->name->toString();
    }
}
