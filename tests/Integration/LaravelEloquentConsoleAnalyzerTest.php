<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tests\Support\FixtureIndexer;

final class LaravelEloquentConsoleAnalyzerTest extends TestCase
{
    public function testLaravel12ModelAndConsoleDocumentsContainExpectedLinks(): void
    {
        $userModelDocument = FixtureIndexer::documentJson('laravel12-app', 'app/Models/User.php');
        $consoleDocument = FixtureIndexer::documentJson('laravel12-app', 'routes/console.php');
        $commandDocument = FixtureIndexer::documentJson('laravel12-app', 'app/Console/Commands/SyncUsersCommand.php');

        self::assertStringContainsString('App/Models/Post#', $userModelDocument);
        self::assertStringContainsString('App/Console/Commands/SyncUsersCommand#', $consoleDocument);
        self::assertStringContainsString('App/Console/Commands/SyncUsersCommand#', $commandDocument);
    }

    public function testLaravel13ModelAndConsoleDocumentsContainExpectedLinks(): void
    {
        $userModelDocument = FixtureIndexer::documentJson('laravel13-app', 'app/Models/User.php');
        $consoleDocument = FixtureIndexer::documentJson('laravel13-app', 'routes/console.php');
        $commandDocument = FixtureIndexer::documentJson('laravel13-app', 'app/Console/Commands/SyncPhotosCommand.php');

        self::assertStringContainsString('App/Models/Photo#', $userModelDocument);
        self::assertStringContainsString('App/Console/Commands/SyncPhotosCommand#', $consoleDocument);
        self::assertStringContainsString('App/Console/Commands/SyncPhotosCommand#', $commandDocument);
    }
}
