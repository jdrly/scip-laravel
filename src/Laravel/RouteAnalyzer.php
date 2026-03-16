<?php

declare(strict_types=1);

namespace ScipLaravel\Laravel;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\PosResolver;
use ScipLaravel\Project\ProjectModel;
use ScipLaravel\Scip\Occurrence;
use ScipLaravel\Scip\SymbolNamer;
use ScipLaravel\Support\FileReader;

use function in_array;
use function str_contains;
use function strpos;
use function substr;

final class RouteAnalyzer
{
    private const array ROUTE_METHODS = [
        'any',
        'delete',
        'get',
        'match',
        'options',
        'patch',
        'post',
        'put',
    ];

    private const array RESOURCE_METHODS = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

    private const array API_RESOURCE_METHODS = ['index', 'store', 'show', 'update', 'destroy'];

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
    public function occurrences(string $absolutePath, array $statements): array
    {
        $positionResolver = new PosResolver(FileReader::read($absolutePath));
        $visitor = new class ($this, $positionResolver) extends NodeVisitorAbstract
        {
            /** @var list<Occurrence> */
            public array $occurrences = [];

            public function __construct(
                private readonly RouteAnalyzer $analyzer,
                private readonly PosResolver $positionResolver,
            ) {
            }

            public function leaveNode(Node $node): ?Node
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
        if (!$node instanceof StaticCall || !$node->name instanceof Identifier || !$node->class instanceof Name) {
            return [];
        }

        if (!$this->isRouteFacade($node->class)) {
            return [];
        }

        $routeMethod = $node->name->toString();

        if (in_array($routeMethod, self::ROUTE_METHODS, true)) {
            return $this->routeMethodOccurrences($node, $positionResolver);
        }

        if ($routeMethod === 'resource') {
            return $this->resourceOccurrences($node, $positionResolver, self::RESOURCE_METHODS);
        }

        if ($routeMethod === 'apiResource') {
            return $this->resourceOccurrences($node, $positionResolver, self::API_RESOURCE_METHODS);
        }

        if ($routeMethod === 'resources') {
            return $this->resourcesOccurrences($node, $positionResolver);
        }

        return [];
    }

    private function isRouteFacade(Name $className): bool
    {
        $resolvedName = $className->getAttribute('resolvedName');
        if ($resolvedName instanceof Name) {
            return $resolvedName->toString() === 'Illuminate\\Support\\Facades\\Route';
        }

        return in_array($className->toString(), ['Route', 'Illuminate\\Support\\Facades\\Route'], true);
    }

    /** @return list<Occurrence> */
    private function routeMethodOccurrences(StaticCall $call, PosResolver $positionResolver): array
    {
        $action = $call->args[1]->value ?? null;
        if (!$action instanceof Expr) {
            return [];
        }

        if ($action instanceof Array_) {
            return $this->arrayActionOccurrences($action, $positionResolver);
        }

        if ($action instanceof ClassConstFetch) {
            $className = $this->classNameFromClassConstFetch($action);
            if ($className === null) {
                return [];
            }

            return [$this->methodOccurrence($className, '__invoke', $action->class, $positionResolver)];
        }

        if ($action instanceof String_) {
            return $this->stringActionOccurrences($action, $call, $positionResolver);
        }

        return [];
    }

    /** @return list<Occurrence> */
    private function arrayActionOccurrences(Array_ $action, PosResolver $positionResolver): array
    {
        $className = null;
        $methodNode = null;

        foreach ($action->items as $item) {
            if ($className === null && $item->value instanceof ClassConstFetch) {
                $className = $this->classNameFromClassConstFetch($item->value);
                continue;
            }

            if ($methodNode === null && $item->value instanceof String_) {
                $methodNode = $item->value;
            }
        }

        if ($className === null || !$methodNode instanceof String_) {
            return [];
        }

        return [$this->methodOccurrence($className, $methodNode->value, $methodNode, $positionResolver)];
    }

    /** @return list<Occurrence> */
    private function stringActionOccurrences(String_ $action, Node $contextNode, PosResolver $positionResolver): array
    {
        if (str_contains($action->value, '@')) {
            $separatorPosition = strpos($action->value, '@');
            if ($separatorPosition === false) {
                return [];
            }

            $className = substr($action->value, 0, $separatorPosition);
            $methodName = substr($action->value, $separatorPosition + 1);
            if ($className === '' || $methodName === '') {
                return [];
            }

            return [
                $this->classOccurrence($className, $action, $positionResolver),
                $this->methodOccurrence($className, $methodName, $action, $positionResolver),
            ];
        }

        $controllerClassName = $this->controllerGroupClassName($contextNode);
        if ($controllerClassName === null) {
            return [];
        }

        return [$this->methodOccurrence($controllerClassName, $action->value, $action, $positionResolver)];
    }

    /**
     * @param list<string> $resourceMethods
     * @return list<Occurrence>
     */
    private function resourceOccurrences(StaticCall $call, PosResolver $positionResolver, array $resourceMethods): array
    {
        $controllerArgument = $call->args[1]->value ?? null;
        if (!$controllerArgument instanceof ClassConstFetch) {
            return [];
        }

        $className = $this->classNameFromClassConstFetch($controllerArgument);
        if ($className === null) {
            return [];
        }

        /** @var list<Occurrence> $occurrences */
        $occurrences = [];
        foreach ($resourceMethods as $methodName) {
            $occurrences[] = $this->methodOccurrence(
                $className,
                $methodName,
                $controllerArgument->class,
                $positionResolver,
            );
        }

        return $occurrences;
    }

    /** @return list<Occurrence> */
    private function resourcesOccurrences(StaticCall $call, PosResolver $positionResolver): array
    {
        $resourcesArgument = $call->args[0]->value ?? null;
        if (!$resourcesArgument instanceof Array_) {
            return [];
        }

        /** @var list<Occurrence> $occurrences */
        $occurrences = [];
        foreach ($resourcesArgument->items as $item) {
            if (!$item->value instanceof ClassConstFetch) {
                continue;
            }

            $className = $this->classNameFromClassConstFetch($item->value);
            if ($className === null) {
                continue;
            }

            foreach (self::RESOURCE_METHODS as $methodName) {
                $occurrences[] = $this->methodOccurrence(
                    $className,
                    $methodName,
                    $item->value->class,
                    $positionResolver,
                );
            }
        }

        return $occurrences;
    }

    private function controllerGroupClassName(Node $node): ?string
    {
        while (($node = $node->getAttribute('parent')) instanceof Node) {
            if (
                !$node instanceof MethodCall
                || !$node->name instanceof Identifier
                || $node->name->toString() !== 'group'
            ) {
                continue;
            }

            $controllerCall = $node->var;
            if (!$controllerCall instanceof StaticCall || !$controllerCall->name instanceof Identifier) {
                continue;
            }

            if ($controllerCall->name->toString() !== 'controller') {
                continue;
            }

            $controllerArgument = $controllerCall->args[0]->value ?? null;
            if (!$controllerArgument instanceof ClassConstFetch) {
                continue;
            }

            return $this->classNameFromClassConstFetch($controllerArgument);
        }

        return null;
    }

    private function classNameFromClassConstFetch(ClassConstFetch $classConstFetch): ?string
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

    private function classOccurrence(string $className, Node $node, PosResolver $positionResolver): Occurrence
    {
        return new Occurrence(
            range: $positionResolver->range($node),
            symbol: $this->symbolNamer->classLike(
                $this->projectModel->composerProject->packageName,
                $this->projectVersion,
                $className,
            ),
            role: 'reference',
            syntaxKind: 'type',
        );
    }

    private function methodOccurrence(
        string $className,
        string $methodName,
        Node $node,
        PosResolver $positionResolver,
    ): Occurrence {
        return new Occurrence(
            range: $positionResolver->range($node),
            symbol: $this->symbolNamer->method(
                $this->projectModel->composerProject->packageName,
                $this->projectVersion,
                $className,
                $methodName,
            ),
            role: 'reference',
            syntaxKind: 'method',
        );
    }
}
