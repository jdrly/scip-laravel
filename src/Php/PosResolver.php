<?php

declare(strict_types=1);

namespace ScipLaravel\Php;

use InvalidArgumentException;
use PhpParser\Node;

use function strrpos;
use function strlen;

final readonly class PosResolver
{
    private int $codeLength;

    public function __construct(private string $code)
    {
        $this->codeLength = strlen($code);
    }

    /** @return array{int, int, int, int} */
    public function range(Node $node): array
    {
        return [
            $node->getStartLine() - 1,
            $this->column($node->getStartFilePos()) - 1,
            $node->getEndLine() - 1,
            $this->column($node->getEndFilePos()),
        ];
    }

    private function column(int $filePosition): int
    {
        $offset = $filePosition - $this->codeLength;
        if ($offset > 0) {
            throw new InvalidArgumentException('Invalid position information.');
        }

        $lineStart = strrpos($this->code, "\n", $offset);
        if ($lineStart === false) {
            $lineStart = -1;
        }

        return $filePosition - $lineStart;
    }
}
