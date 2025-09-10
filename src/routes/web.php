<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// 管理者ログイン画面
Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['guest:admin'])
    ->name('admin.login');

// 管理者ログイン処理（Fortify に任せる）
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest:admin']);



Route::middleware(['auth:admin'])->group(function () {
    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('admin.logout');
});
