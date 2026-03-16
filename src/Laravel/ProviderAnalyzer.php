<?php

declare(strict_types=1);

namespace ScipLaravel\Laravel;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\PosResolver;
use ScipLaravel\Project\ProjectModel;
use ScipLaravel\Scip\Occurrence;
use ScipLaravel\Scip\SymbolNamer;
use ScipLaravel\Support\FileReader;

use function in_array;
use function is_string;

final class ProviderAnalyzer
{
    public function __construct(
        private readonly AstTraverser $traverser,
        private readonly SymbolNamer $symbolNamer,
        private readonly ProjectModel $projectModel,
        private readonly ProviderBindingMap $bindingMap,
        private readonly string $projectVersion,
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
                private readonly ProviderAnalyzer $analyzer,
                private readonly string $relativePath,
                private readonly PosResolver $positionResolver,
            ) {
            }

            public function enterNode(Node $node): ?Node
            {
                foreach (
                    $this->analyzer->analyzeNode(
                        $node,
                        $this->relativePath,
                        $this->positionResolver,
                    ) as $occurrence
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
        if ($relativePath === 'bootstrap/providers.php') {
            return $this->providerRegistrationOccurrences($node, $positionResolver);
        }

        if (
            !in_array($relativePath, $this->projectModel->providerFiles, true)
            || $relativePath === 'bootstrap/providers.php'
        ) {
            return [];
        }

        $definitions = $this->bindingMap->forSourcePath($relativePath);
        if ($definitions === []) {
            return [];
        }

        if ($node instanceof Property) {
            return $this->propertyBindingOccurrences($node, $positionResolver);
        }

        if ($node instanceof MethodCall) {
            return $this->methodCallBindingOccurrences($node, $positionResolver);
        }

        if ($node instanceof StaticCall) {
            return $this->staticCallBindingOccurrences($node, $positionResolver);
        }

        return [];
    }

    /** @return list<Occurrence> */
    private function providerRegistrationOccurrences(Node $node, PosResolver $positionResolver): array
    {
        if (!$node instanceof ClassConstFetch) {
            return [];
        }

        $className = $this->className($node);
        if ($className === null) {
            return [];
        }

        return [$this->classOccurrence($className, $node->class, $positionResolver)];
    }

    /** @return list<Occurrence> */
    private function propertyBindingOccurrences(Property $property, PosResolver $positionResolver): array
    {
        $propertyName = $property->props[0]->name->toString();
        if (!in_array($propertyName, ['bindings', 'singletons'], true)) {
            return [];
        }

        $defaultValue = $property->props[0]->default;
        if (!$defaultValue instanceof Array_) {
            return [];
        }

        return $this->bindingOccurrencesFromArray($defaultValue, $positionResolver);
    }

    /** @return list<Occurrence> */
    private function methodCallBindingOccurrences(MethodCall $methodCall, PosResolver $positionResolver): array
    {
        if (!$methodCall->name instanceof Identifier || !$methodCall->var instanceof PropertyFetch) {
            return [];
        }

        if (!$methodCall->var->var instanceof Variable || !is_string($methodCall->var->var->name)) {
            return [];
        }

        if (!$methodCall->var->name instanceof Identifier || $methodCall->var->name->toString() !== 'app') {
            return [];
        }

        if ($methodCall->var->var->name !== 'this') {
            return [];
        }

        if (!in_array($methodCall->name->toString(), ['bind', 'singleton', 'scoped'], true)) {
            return [];
        }

        /** @var list<\PhpParser\Node\Arg> $args */
        $args = [];
        foreach ($methodCall->args as $arg) {
            if ($arg instanceof \PhpParser\Node\Arg) {
                $args[] = $arg;
            }
        }

        return $this->bindingOccurrencesFromArgs($args, $positionResolver);
    }

    /** @return list<Occurrence> */
    private function staticCallBindingOccurrences(StaticCall $staticCall, PosResolver $positionResolver): array
    {
        if (!$staticCall->name instanceof Identifier || !$staticCall->class instanceof Name) {
            return [];
        }

        if (!in_array($staticCall->name->toString(), ['bind', 'singleton'], true)) {
            return [];
        }

        $resolvedName = $staticCall->class->getAttribute('resolvedName');
        $className = $resolvedName instanceof Name ? $resolvedName->toString() : $staticCall->class->toString();
        if (!in_array($className, ['App', 'Illuminate\\Support\\Facades\\App'], true)) {
            return [];
        }

        /** @var list<\PhpParser\Node\Arg> $args */
        $args = [];
        foreach ($staticCall->args as $arg) {
            if ($arg instanceof \PhpParser\Node\Arg) {
                $args[] = $arg;
            }
        }

        return $this->bindingOccurrencesFromArgs($args, $positionResolver);
    }

    /** @return list<Occurrence> */
    private function bindingOccurrencesFromArray(Array_ $array, PosResolver $positionResolver): array
    {
        /** @var list<Occurrence> $occurrences */
        $occurrences = [];

        foreach ($array->items as $item) {
            $abstract = $item->key instanceof ClassConstFetch ? $item->key : null;
            $concrete = $item->value instanceof ClassConstFetch ? $item->value : null;
            if (!$abstract instanceof ClassConstFetch || !$concrete instanceof ClassConstFetch) {
                continue;
            }

            $abstractClassName = $this->className($abstract);
            $concreteClassName = $this->className($concrete);
            if ($abstractClassName === null || $concreteClassName === null) {
                continue;
            }

            $occurrences[] = $this->classOccurrence($abstractClassName, $abstract->class, $positionResolver);
            $occurrences[] = $this->classOccurrence($concreteClassName, $concrete->class, $positionResolver);
        }

        return $occurrences;
    }

    /**
     * @param list<\PhpParser\Node\Arg> $args
     * @return list<Occurrence>
     */
    private function bindingOccurrencesFromArgs(array $args, PosResolver $positionResolver): array
    {
        $abstract = $args[0]->value ?? null;
        $concrete = $args[1]->value ?? null;
        if (!$abstract instanceof ClassConstFetch || !$concrete instanceof ClassConstFetch) {
            return [];
        }

        $abstractClassName = $this->className($abstract);
        $concreteClassName = $this->className($concrete);
        if ($abstractClassName === null || $concreteClassName === null) {
            return [];
        }

        return [
            $this->classOccurrence($abstractClassName, $abstract->class, $positionResolver),
            $this->classOccurrence($concreteClassName, $concrete->class, $positionResolver),
        ];
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
}
