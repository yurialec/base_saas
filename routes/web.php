<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::domain(RouteServiceProvider::applicationDomain())->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::domain(RouteServiceProvider::tenantDomain())->group(function () {
    Route::get('/auth/handoff/{token}', [LoginController::class, 'consumeHandoff'])
        ->middleware(['signed', 'throttle:10,1'])
        ->where('token', '[A-Za-z0-9]{64}')
        ->name('tenant.auth.handoff');

    Route::middleware(['auth', 'tenant'])->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('tenant.logout');
        Route::get('/{any?}', function () {
            return view('layouts.app_admin');
        })->where('any', '.*');
    });
});
