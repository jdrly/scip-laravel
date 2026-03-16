<?php

declare(strict_types=1);

namespace Tests\Unit\Cli;

use PHPUnit\Framework\TestCase;
use ScipLaravel\Cli\ApplicationFactory;

final class ApplicationFactoryTest extends TestCase
{
    public function testCreateReturnsConfiguredApplication(): void
    {
        $application = ApplicationFactory::create();

        self::assertSame('scip-laravel', $application->getName());
        self::assertSame(ApplicationFactory::VERSION, $application->getVersion());
    }
}
