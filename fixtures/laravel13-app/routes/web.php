<?php

declare(strict_types=1);

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::post('/health', HealthCheckController::class);
Route::controller(HomeController::class)->group(function (): void {
    Route::get('/about', 'about');
});
Route::resources([
    'photos' => PhotoController::class,
]);
