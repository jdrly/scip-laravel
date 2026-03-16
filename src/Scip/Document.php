<?php

declare(strict_types=1);

namespace ScipLaravel\Scip;

final readonly class Document implements \JsonSerializable
{
    /**
     * @param list<Occurrence> $occurrences
     * @param list<SymbolInformation> $symbols
     */
    public function __construct(
        public string $relativePath,
        /** @var list<Occurrence> */
        public array $occurrences,
        /** @var list<SymbolInformation> */
        public array $symbols,
    ) {
    }

    /** @return array{occurrences: list<Occurrence>, relativePath: string, symbols: list<SymbolInformation>} */
    public function jsonSerialize(): array
    {
        return [
            'occurrences' => $this->occurrences,
            'relativePath' => $this->relativePath,
            'symbols' => $this->symbols,
        ];
    }
}
