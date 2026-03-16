<?php

declare(strict_types=1);

namespace ScipLaravel\Scip;

final readonly class Occurrence implements \JsonSerializable
{
    /** @param array{int, int, int, int} $range */
    public function __construct(
        public array $range,
        public string $symbol,
        public string $role,
        public string $syntaxKind,
    ) {
    }

    /** @return array{range: array{int, int, int, int}, role: string, symbol: string, syntaxKind: string} */
    public function jsonSerialize(): array
    {
        return [
            'range' => $this->range,
            'role' => $this->role,
            'symbol' => $this->symbol,
            'syntaxKind' => $this->syntaxKind,
        ];
    }
}
