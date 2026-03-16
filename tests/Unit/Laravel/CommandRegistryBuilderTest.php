<?php

declare(strict_types=1);

namespace Tests\Unit\Laravel;

use PHPUnit\Framework\TestCase;
use ScipLaravel\Laravel\CommandRegistryBuilder;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\Parser;
use ScipLaravel\Project\ProjectModelDetector;
use ScipLaravel\Scip\SymbolNamer;
use Tests\Support\FixturePaths;

final class CommandRegistryBuilderTest extends TestCase
{
    private CommandRegistryBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new CommandRegistryBuilder(new Parser(), new AstTraverser(), new SymbolNamer());
    }

    public function testBuildCollectsLaravel12Commands(): void
    {
        $projectModel = (new ProjectModelDetector())->detect(FixturePaths::fixture('laravel12-app'));
        $registry = $this->builder->build($projectModel, 'dev');

        $command = $registry->findBySignature('sync:users');
        self::assertNotNull($command);
        self::assertSame('app/Console/Commands/SyncUsersCommand.php', $command->sourcePath);
        self::assertStringContainsString('App/Console/Commands/SyncUsersCommand#', $command->classSymbol);
    }

    public function testBuildCollectsLaravel13Commands(): void
    {
        $projectModel = (new ProjectModelDetector())->detect(FixturePaths::fixture('laravel13-app'));
        $registry = $this->builder->build($projectModel, 'dev');

        $command = $registry->findBySignature('sync:photos');
        self::assertNotNull($command);
        self::assertSame('app/Console/Commands/SyncPhotosCommand.php', $command->sourcePath);
        self::assertStringContainsString('App/Console/Commands/SyncPhotosCommand#', $command->classSymbol);
    }
}
