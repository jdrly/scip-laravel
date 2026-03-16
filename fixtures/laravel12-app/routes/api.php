<?php

declare(strict_types=1);

use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

Route::apiResource('photos', PhotoController::class);
