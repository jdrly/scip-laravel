<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        User::class => Post::class,
    ];

    public array $singletons = [
        Post::class => User::class,
    ];

    public function register(): void
    {
        $this->app->bind(User::class, Post::class);
        $this->app->singleton(User::class, Post::class);
        $this->app->scoped(Post::class, User::class);
    }
}
