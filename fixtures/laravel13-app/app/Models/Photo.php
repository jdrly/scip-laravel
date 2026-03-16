<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Photo
{
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
