<?php

declare(strict_types=1);

namespace Tests\Unit\Project;

use PHPUnit\Framework\TestCase;
use ScipLaravel\Project\ProjectModelDetector;
use Tests\Support\FixturePaths;

final class ProjectModelDetectorTest extends TestCase
{
    private ProjectModelDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new ProjectModelDetector();
    }

    public function testDetectRecognizesPlainPhpFixture(): void
    {
        $model = $this->detector->detect(FixturePaths::fixture('plain-php-modern'));

        self::assertSame('plain-php', $model->framework);
        self::assertNull($model->laravelVersion);
        self::assertSame([], $model->routeFiles);
    }

    public function testDetectRecognizesLaravel12Fixture(): void
    {
        $model = $this->detector->detect(FixturePaths::fixture('laravel12-app'));

        self::assertSame('laravel', $model->framework);
        self::assertSame('12', $model->laravelVersion);
        self::assertTrue($model->hasBootstrapApp);
        self::assertTrue($model->hasBootstrapProviders);
        self::assertSame(['routes/web.php', 'routes/api.php', 'routes/console.php'], $model->routeFiles);
        self::assertContains('bootstrap/providers.php', $model->providerFiles);
        self::assertContains('app/Providers/AppServiceProvider.php', $model->providerFiles);
        self::assertSame(['app/Http/Controllers'], $model->controllerDirectories);
        self::assertSame(['app/Models'], $model->modelDirectories);
        self::assertSame(['app/Console/Commands'], $model->commandDirectories);
    }

    public function testDetectRecognizesLaravel13Fixture(): void
    {
        $model = $this->detector->detect(FixturePaths::fixture('laravel13-app'));

        self::assertSame('laravel', $model->framework);
        self::assertSame('13', $model->laravelVersion);
        self::assertTrue($model->hasBootstrapApp);
        self::assertTrue($model->hasBootstrapProviders);
        self::assertSame(['routes/web.php', 'routes/api.php', 'routes/console.php'], $model->routeFiles);
    }
}
