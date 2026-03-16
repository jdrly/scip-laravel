<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

final class User
{
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}
