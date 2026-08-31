<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/sidebar', [HomeController::class, 'getSideBar']);
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [UserController::class, 'updateProfile'])->name('update.profile');

    Route::prefix('menus')->middleware('acl:menus')->group(function () {
        Route::get('/list', [MenuController::class, 'index'])->name('menus.index');
        Route::get('/find/{id}', [MenuController::class, 'show'])->whereNumber('id')->name('menus.show');
        Route::get('/list-to-create', [MenuController::class, 'listToCreate'])->name('menus.list-to-create');
        Route::post('/store', [MenuController::class, 'store'])->name('menus.store');
        Route::put('/update/{id}', [MenuController::class, 'update'])->whereNumber('id')->name('menus.update');
        Route::delete('/{id}', [MenuController::class, 'destroy'])->whereNumber('id')->name('menus.destroy');
        Route::post('/change-menu-order/{id}', [MenuController::class, 'changeMenuOrder'])->whereNumber('id')->name('menus.changeMenuOrder');
    });

    Route::prefix('roles')->middleware('acl:roles')->group(function () {
        Route::get('/list', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/dropdown-list', [RoleController::class, 'dropdownList']);
        Route::post('/store', [RoleController::class, 'store']);
        Route::get('/find/{id}', [RoleController::class, 'show'])->whereNumber('id')->name('roles.show');
        Route::put('/update/{id}', [RoleController::class, 'update'])->whereNumber('id')->name('roles.update');
        Route::delete('/delete/{id}', [RoleController::class, 'delete'])->whereNumber('id')->name('roles.delete');
    });

    Route::prefix('permissions')->middleware('acl:permissions')->group(function () {
        Route::get('/list', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/store', [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('/find/{id}', [PermissionController::class, 'show'])->whereNumber('id')->name('permissions.show');
        Route::put('/update/{id}', [PermissionController::class, 'update'])->whereNumber('id')->name('permissions.update');
        Route::delete('/delete/{id}', [PermissionController::class, 'destroy'])->whereNumber('id')->name('permissions.delete');
    });
    
    Route::prefix('users')->middleware('acl:users')->group(function () {
        Route::get('/list', [UserController::class, 'index'])->name('users.index');
        Route::post('/store', [UserController::class, 'store'])->name('users.store');
        Route::get('/find/{id}', [UserController::class, 'show'])->whereNumber('id')->name('users.show');
        Route::put('/update/{id}', [UserController::class, 'update'])->whereNumber('id')->name('users.update');
        Route::delete('/delete/{id}', [UserController::class, 'destroy'])->whereNumber('id')->name('users.delete');
        Route::get('/roles/list', [UserController::class, 'listRoles'])->name('users.roles');
    });
});
