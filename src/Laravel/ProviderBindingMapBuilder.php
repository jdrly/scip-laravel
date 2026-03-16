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
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\Parser;
use ScipLaravel\Project\ProjectModel;

use function in_array;
use function is_string;

final class ProviderBindingMapBuilder
{
    public function __construct(
        private readonly Parser $parser,
        private readonly AstTraverser $traverser,
    ) {
    }

    public function build(ProjectModel $projectModel): ProviderBindingMap
    {
        /** @var list<BindingDefinition> $definitions */
        $definitions = [];

        foreach ($projectModel->providerFiles as $providerPath) {
            if ($providerPath === 'bootstrap/providers.php') {
                continue;
            }

            $absolutePath = $projectModel->composerProject->rootPath . '/' . $providerPath;
            $statements = $this->parser->parseFile($absolutePath);

            $visitor = new class ($this, $providerPath) extends NodeVisitorAbstract
            {
                /** @var list<BindingDefinition> */
                public array $definitions = [];

                public function __construct(
                    private readonly ProviderBindingMapBuilder $builder,
                    private readonly string $providerPath,
                ) {
                }

                public function enterNode(Node $node): ?Node
                {
                    foreach ($this->builder->definitionsForNode($node, $this->providerPath) as $definition) {
                        $this->definitions[] = $definition;
                    }

                    return null;
                }
            };

            $this->traverser->traverse($statements, $visitor);
            foreach ($visitor->definitions as $definition) {
                $definitions[] = $definition;
            }
        }

        return new ProviderBindingMap($definitions);
    }

    /** @return list<BindingDefinition> */
    public function definitionsForNode(Node $node, string $providerPath): array
    {
        if ($node instanceof Property) {
            return $this->propertyDefinitions($node, $providerPath);
        }

        if ($node instanceof MethodCall) {
            return $this->methodCallDefinitions($node, $providerPath);
        }

        if ($node instanceof StaticCall) {
            return $this->staticCallDefinitions($node, $providerPath);
        }

        return [];
    }

    /** @return list<BindingDefinition> */
    private function propertyDefinitions(Property $property, string $providerPath): array
    {
        $kind = null;
        foreach ($property->props as $propertyItem) {
            $name = $propertyItem->name->toString();
            if ($name === 'bindings') {
                $kind = 'bindings-property';
            }

            if ($name === 'singletons') {
                $kind = 'singletons-property';
            }
        }

        if ($kind === null || !$property->props[0]->default instanceof Array_) {
            return [];
        }

        return $this->bindingDefinitionsFromArray($property->props[0]->default, $providerPath, $kind);
    }

    /** @return list<BindingDefinition> */
    private function methodCallDefinitions(MethodCall $methodCall, string $providerPath): array
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

        $kind = $methodCall->name->toString();
        if (!in_array($kind, ['bind', 'singleton', 'scoped'], true)) {
            return [];
        }

        /** @var list<\PhpParser\Node\Arg> $args */
        $args = [];
        foreach ($methodCall->args as $arg) {
            if ($arg instanceof \PhpParser\Node\Arg) {
                $args[] = $arg;
            }
        }

        /** @var list<BindingDefinition> $definitions */
        $definitions = $this->bindingDefinitionsFromCallArgs($args, $providerPath, $kind . '-call');

        return $definitions;
    }

    /** @return list<BindingDefinition> */
    private function staticCallDefinitions(StaticCall $staticCall, string $providerPath): array
    {
        if (!$staticCall->name instanceof Identifier || !$staticCall->class instanceof Name) {
            return [];
        }

        $kind = $staticCall->name->toString();
        if (!in_array($kind, ['bind', 'singleton'], true)) {
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

        /** @var list<BindingDefinition> $definitions */
        $definitions = $this->bindingDefinitionsFromCallArgs($args, $providerPath, 'app-facade-' . $kind);

        return $definitions;
    }

    /** @return list<BindingDefinition> */
    private function bindingDefinitionsFromArray(Array_ $array, string $providerPath, string $kind): array
    {
        /** @var list<BindingDefinition> $definitions */
        $definitions = [];

        foreach ($array->items as $item) {
            $abstract = $item->key instanceof ClassConstFetch ? $this->className($item->key) : null;
            $concrete = $item->value instanceof ClassConstFetch ? $this->className($item->value) : null;
            if ($abstract === null || $concrete === null) {
                continue;
            }

            $definitions[] = new BindingDefinition($providerPath, $abstract, $concrete, $kind);
        }

        return $definitions;
    }

    /**
     * @param list<\PhpParser\Node\Arg> $args
     * @return list<BindingDefinition>
     */
    private function bindingDefinitionsFromCallArgs(array $args, string $providerPath, string $kind): array
    {
        $abstract = $args[0]->value ?? null;
        $concrete = $args[1]->value ?? null;

        if (!$abstract instanceof ClassConstFetch || !$concrete instanceof ClassConstFetch) {
            return [];
        }

        $abstractClass = $this->className($abstract);
        $concreteClass = $this->className($concrete);
        if ($abstractClass === null || $concreteClass === null) {
            return [];
        }

        return [new BindingDefinition($providerPath, $abstractClass, $concreteClass, $kind)];
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
