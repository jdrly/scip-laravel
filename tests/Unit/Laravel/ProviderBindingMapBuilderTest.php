<?php

declare(strict_types=1);

namespace Tests\Unit\Laravel;

use PHPUnit\Framework\TestCase;
use ScipLaravel\Laravel\ProviderBindingMapBuilder;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\Parser;
use ScipLaravel\Project\ProjectModelDetector;
use Tests\Support\FixturePaths;

final class ProviderBindingMapBuilderTest extends TestCase
{
    private ProviderBindingMapBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new ProviderBindingMapBuilder(new Parser(), new AstTraverser());
    }

    public function testBuildCollectsLaravel12Bindings(): void
    {
        $projectModel = (new ProjectModelDetector())->detect(FixturePaths::fixture('laravel12-app'));
        $bindingMap = $this->builder->build($projectModel);

        $definitions = $bindingMap->forSourcePath('app/Providers/AppServiceProvider.php');
        self::assertCount(5, $definitions);
        self::assertSame('bindings-property', $definitions[0]->kind);
        self::assertSame('singletons-property', $definitions[1]->kind);
        self::assertSame('bind-call', $definitions[2]->kind);
        self::assertSame('singleton-call', $definitions[3]->kind);
        self::assertSame('scoped-call', $definitions[4]->kind);
    }

    public function testBuildCollectsLaravel13Bindings(): void
    {
        $projectModel = (new ProjectModelDetector())->detect(FixturePaths::fixture('laravel13-app'));
        $bindingMap = $this->builder->build($projectModel);

        $definitions = $bindingMap->forSourcePath('app/Providers/AppServiceProvider.php');
        self::assertCount(4, $definitions);
        self::assertSame('bindings-property', $definitions[0]->kind);
        self::assertSame('singletons-property', $definitions[1]->kind);
        self::assertSame('app-facade-bind', $definitions[2]->kind);
        self::assertSame('app-facade-singleton', $definitions[3]->kind);
    }
}
