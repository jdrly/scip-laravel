<?php

declare(strict_types=1);

namespace ScipLaravel\Scip;

final readonly class SymbolInformation implements \JsonSerializable
{
    public function __construct(
        public string $symbol,
        public string $kind,
    ) {
    }

    /** @return array{kind: string, symbol: string} */
    public function jsonSerialize(): array
    {
        return [
            'kind' => $this->kind,
            'symbol' => $this->symbol,
        ];
    }
}
