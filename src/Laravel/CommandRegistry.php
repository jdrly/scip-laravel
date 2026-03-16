<?php

declare(strict_types=1);

namespace ScipLaravel\Laravel;

final readonly class CommandRegistry
{
    /** @param list<CommandDefinition> $definitions */
    public function __construct(public array $definitions)
    {
    }

    public function findBySignature(string $signature): ?CommandDefinition
    {
        foreach ($this->definitions as $definition) {
            if ($definition->signature === $signature) {
                return $definition;
            }
        }

        return null;
    }

    /** @return list<CommandDefinition> */
    public function forSourcePath(string $sourcePath): array
    {
        $definitions = [];

        foreach ($this->definitions as $definition) {
            if ($definition->sourcePath === $sourcePath) {
                $definitions[] = $definition;
            }
        }

        return $definitions;
    }
}
