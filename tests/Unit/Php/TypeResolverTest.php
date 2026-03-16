<?php

declare(strict_types=1);

namespace Tests\Unit\Php;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPUnit\Framework\TestCase;
use ScipLaravel\Php\TypeResolver;

final class TypeResolverTest extends TestCase
{
    private TypeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new TypeResolver();
    }

    public function testResolveExpressionClassNameReturnsCurrentClassForThisVariable(): void
    {
        $className = $this->resolver->resolveExpressionClassName(new Variable('this'), 'App\\Demo\\Example');

        self::assertSame('App\\Demo\\Example', $className);
    }

    public function testResolveExpressionClassNameReturnsRememberedVariableType(): void
    {
        $this->resolver->enterScope();
        $this->resolver->rememberVariableType('service', 'App\\Support\\Service');

        $className = $this->resolver->resolveExpressionClassName(new Variable('service'));

        self::assertSame('App\\Support\\Service', $className);
    }

    public function testResolveExpressionClassNameReturnsClassNameFromNewExpression(): void
    {
        $className = $this->resolver->resolveExpressionClassName(new New_(new Name('App\\Support\\Service')));

        self::assertSame('App\\Support\\Service', $className);
    }

    public function testScopeExitClearsRememberedVariableTypes(): void
    {
        $this->resolver->enterScope();
        $this->resolver->rememberVariableType('service', 'App\\Support\\Service');
        $this->resolver->leaveScope();

        $className = $this->resolver->resolveExpressionClassName(new Variable('service'));

        self::assertNull($className);
    }
}
