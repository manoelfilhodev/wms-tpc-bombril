<?php

use App\Http\Controllers\Auth\MicrosoftController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth', 'throttle:critical'])->name('logout');

Route::get('/login/microsoft', [MicrosoftController::class, 'redirectToProvider'])->name('login.microsoft');
Route::get('/login/microsoft/callback', [MicrosoftController::class, 'handleProviderCallback']);

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:critical');
