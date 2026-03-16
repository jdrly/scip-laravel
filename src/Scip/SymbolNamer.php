<?php

declare(strict_types=1);

namespace ScipLaravel\Scip;

use Webmozart\Assert\Assert;

final class SymbolNamer
{
    private const string SCHEME = 'scip-laravel';

    private const string MANAGER = 'composer';

    public function classLike(string $packageName, string $version, string $className): string
    {
        return $this->symbol($packageName, $version, $this->normalize($className) . '#');
    }

    public function method(string $packageName, string $version, string $className, string $methodName): string
    {
        Assert::stringNotEmpty($methodName);

        return $this->symbol(
            $packageName,
            $version,
            $this->normalize($className) . '#' . $methodName . '().',
        );
    }

    public function property(string $packageName, string $version, string $className, string $propertyName): string
    {
        Assert::stringNotEmpty($propertyName);

        return $this->symbol(
            $packageName,
            $version,
            $this->normalize($className) . '#$' . ltrim($propertyName, '$') . '.',
        );
    }

    private function symbol(string $packageName, string $version, string $descriptor): string
    {
        Assert::stringNotEmpty($packageName);
        Assert::stringNotEmpty($version);
        Assert::stringNotEmpty($descriptor);

        return self::SCHEME
            . ' '
            . self::MANAGER
            . ' '
            . $packageName
            . ' '
            . $version
            . ' '
            . $descriptor;
    }

    private function normalize(string $identifier): string
    {
        Assert::stringNotEmpty($identifier);

        return str_replace('\\', '/', ltrim($identifier, '\\'));
    }
}
