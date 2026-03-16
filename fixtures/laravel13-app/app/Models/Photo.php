<?php

declare(strict_types=1);

namespace App\Models;

final class Photo
{
    public function owner(): User
    {
        return new User();
    }
}
