<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->name('auth.')->group(function () {
    Route::get('/current', [AuthController::class, 'current'])->middleware(['auth'])->name('current');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth'])->name('logout');

    Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', /*'throttle:10:1'*/])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->middleware(['guest', /*'throttle:10:1'*/])->name('register');

    Route::get('/login/redirect', [AuthController::class, 'redirect'])->middleware(['guest', /*'throttle:10:1'*/])->name('redirect');
    Route::post('/login/callback', [AuthController::class, 'callback'])->middleware(['guest', /*'throttle:10:1'*/])->name('callback');
});

Route::fallback(function () {
    return response()->json(['message' => 'Endpoint Not Found'], 404);
});
