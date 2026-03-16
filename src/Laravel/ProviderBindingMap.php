<?php

declare(strict_types=1);

namespace ScipLaravel\Laravel;

final readonly class ProviderBindingMap
{
    /** @param list<BindingDefinition> $definitions */
    public function __construct(public array $definitions)
    {
    }

    /** @return list<BindingDefinition> */
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
