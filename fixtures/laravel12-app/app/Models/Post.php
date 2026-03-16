<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Post
{
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
