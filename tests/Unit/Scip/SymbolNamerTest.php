<?php

declare(strict_types=1);

namespace Tests\Unit\Scip;

use PHPUnit\Framework\TestCase;
use ScipLaravel\Scip\SymbolNamer;

final class SymbolNamerTest extends TestCase
{
    private SymbolNamer $namer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->namer = new SymbolNamer();
    }

    public function testClassLikeNameUsesScipLaravelScheme(): void
    {
        $symbol = $this->namer->classLike('acme/demo', '1.2.3', 'App\\Models\\User');

        self::assertSame('scip-laravel composer acme/demo 1.2.3 App/Models/User#', $symbol);
    }

    public function testMethodNameUsesNormalizedClassPath(): void
    {
        $symbol = $this->namer->method('acme/demo', '1.2.3', '\\App\\Http\\Controllers\\HomeController', 'index');

        self::assertSame(
            'scip-laravel composer acme/demo 1.2.3 App/Http/Controllers/HomeController#index().',
            $symbol,
        );
    }

    public function testPropertyNameNormalizesLeadingDollar(): void
    {
        $symbol = $this->namer->property('acme/demo', '1.2.3', 'App\\Models\\User', '$email');

        self::assertSame('scip-laravel composer acme/demo 1.2.3 App/Models/User#$email.', $symbol);
    }
}
