<?php

declare(strict_types=1);

namespace App\Models;

final class Post
{
    public function author(): User
    {
        return new User();
    }
}
