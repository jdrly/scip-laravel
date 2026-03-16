<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        User::class => Photo::class,
    ];

    public array $singletons = [
        Photo::class => User::class,
    ];

    public function register(): void
    {
        App::bind(User::class, Photo::class);
        App::singleton(User::class, Photo::class);
    }
}
