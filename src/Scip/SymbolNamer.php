<?php

declare(strict_types=1);

namespace ScipLaravel\Scip;

use Webmozart\Assert\Assert;

use function count;
use function explode;
use function ltrim;
use function rtrim;
use function str_contains;
use function str_replace;
use function strpos;
use function substr;

final class SymbolNamer
{
    private const string SCHEME = 'scip-laravel';

    private const string MANAGER = 'composer';

    public function classLike(string $packageName, string $version, string $className): string
    {
        return $this->symbol($packageName, $version, $this->normalize($className) . '#');
    }

    public function function(string $packageName, string $version, string $functionName): string
    {
        return $this->symbol($packageName, $version, $this->normalize($functionName) . '().');
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

    public function extractIdentifier(string $symbol): string
    {
        $parts = explode(' ', $symbol);
        if (count($parts) !== 5) {
            throw new \RuntimeException("Invalid symbol: $symbol.");
        }

        $descriptor = $parts[4];
        $memberPosition = strpos($descriptor, '#');
        if ($memberPosition !== false) {
            $descriptor = substr($descriptor, 0, $memberPosition);
        }

        $descriptor = str_replace('/', '\\', $descriptor);
        $descriptor = rtrim($descriptor, '.');
        $descriptor = rtrim($descriptor, '()');

        if ($descriptor === '') {
            throw new \LogicException("Cannot extract identifier from symbol: $symbol.");
        }

        return $descriptor;
    }

    public function isFunctionSymbol(string $symbol): bool
    {
        return !str_contains($symbol, '#');
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
