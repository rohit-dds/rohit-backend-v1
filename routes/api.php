<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;

Route::controller(UserController::class)->prefix('/user')->group(function () {
    Route::post('/store','storeUser');
    Route::post('/login','login');

    Route::middleware('auth:sanctum')->group(function () {
            Route::get('/profile', 'profile');
            Route::post('/logout', 'logout');
    });
});

