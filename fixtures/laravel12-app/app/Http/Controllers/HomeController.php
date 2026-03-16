<?php

declare(strict_types=1);

namespace App\Http\Controllers;

final class HomeController
{
    public function index(): string
    {
        return 'home';
    }

    public function about(): string
    {
        return 'about';
    }
}
