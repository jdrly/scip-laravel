<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

final class User
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
