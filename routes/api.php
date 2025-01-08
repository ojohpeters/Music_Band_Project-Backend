<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MusicsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventsController;
// use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\PurchasesController;

Route::resource('music-tracks', MusicsController::class);
Route::resource('events', EventsController::class);
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::resource('users', UserController::class);
    Route::resource('purchases', PurchasesController::class);
 
});
// Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {
//     Route::get('/admins', [AdminController::class, 'users']);
 
// });


