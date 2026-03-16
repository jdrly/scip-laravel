<?php

declare(strict_types=1);

namespace App\Http\Controllers;

final class HealthCheckController
{
    public function __invoke(): string
    {
        return 'ok';
    }
}
