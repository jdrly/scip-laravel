<?php

declare(strict_types=1);

namespace ScipLaravel\Php;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;

use function array_pop;
use function is_string;
use function str_contains;
use function strrpos;
use function substr;

final class TypeResolver
{
    /** @var list<array<string, string>> */
    private array $scopes = [];

    public function enterScope(): void
    {
        $this->scopes[] = [];
    }

    public function leaveScope(): void
    {
        array_pop($this->scopes);
    }

    public function rememberVariableType(string $variableName, string $className): void
    {
        if ($this->scopes === []) {
            return;
        }

        $this->scopes[array_key_last($this->scopes)][$variableName] = $className;
    }

    public function resolveExpressionClassName(Expr $expression, ?string $currentClassName = null): ?string
    {
        if ($expression instanceof New_ && $expression->class instanceof Name) {
            return $this->qualifyClassName($this->resolveName($expression->class), $currentClassName);
        }

        if ($expression instanceof Variable && is_string($expression->name)) {
            if ($expression->name === 'this') {
                return $currentClassName;
            }

            return $this->resolveVariableType($expression->name);
        }

        return null;
    }

    public function resolveName(Name $name): string
    {
        $resolvedName = $name->getAttribute('resolvedName');
        if ($resolvedName instanceof Name) {
            return $resolvedName->toString();
        }

        $namespacedName = $name->getAttribute('namespacedName');
        if ($namespacedName instanceof Name) {
            return $namespacedName->toString();
        }

        return $name->toString();
    }

    private function resolveVariableType(string $variableName): ?string
    {
        for ($i = count($this->scopes) - 1; $i >= 0; $i--) {
            if (isset($this->scopes[$i][$variableName])) {
                return $this->scopes[$i][$variableName];
            }
        }

        return null;
    }

    private function qualifyClassName(string $className, ?string $currentClassName): string
    {
        if (str_contains($className, '\\') || $currentClassName === null) {
            return $className;
        }

        $namespacePosition = strrpos($currentClassName, '\\');
        if ($namespacePosition === false) {
            return $className;
        }

        return substr($currentClassName, 0, $namespacePosition) . '\\' . $className;
    }
}
