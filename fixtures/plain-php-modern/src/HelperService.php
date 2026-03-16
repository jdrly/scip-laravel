<?php

declare(strict_types=1);

namespace Fixture\PlainPhp;

final class HelperService
{
    public function format(string $value): string
    {
        return strtoupper($value);
    }
}
