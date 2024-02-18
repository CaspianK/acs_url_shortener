<?php

use App\Http\Controllers\LinkController;
use App\Http\Controllers\SuperuserController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [UserController::class, 'login']);

Route::get('/{user}/{token}', [LinkController::class, 'redirect']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('users')->group(function () {
        Route::post('/create', [SuperuserController::class, 'createUser']);
    });
    Route::prefix('links')->group(function () {
        Route::get('/', [LinkController::class, 'index']);
        Route::post('/create', [LinkController::class, 'store']);
        Route::get('/{link}', [LinkController::class, 'show']);
    });
});
