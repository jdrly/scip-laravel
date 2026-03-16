<?php

declare(strict_types=1);

namespace App\Http\Controllers;

final class PhotoController
{
    public function index(): string
    {
        return 'index';
    }

    public function show(int $id): string
    {
        return (string) $id;
    }

    public function store(): string
    {
        return 'store';
    }
}
