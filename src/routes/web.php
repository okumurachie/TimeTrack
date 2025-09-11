<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\User\AttendanceController as UserAttendanceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


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

// Route::get('/', function () {
//     return view('welcome');
// });


// 管理者ログイン画面
Route::get('/admin/login', [AdminAuthenticatedSessionController::class, 'create'])
    ->middleware(['guest:admin'])
    ->name('admin.login');

// 管理者ログイン処理
Route::post('/admin/login', [AdminAuthenticatedSessionController::class, 'store'])
    ->middleware(['guest:admin']);

// 管理者専用ルート
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {

    Route::get('/attendance/list', [AdminAttendanceController::class, 'index']);


    Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])
        ->name('admin.logout');
});


//仮表示用
// Route::get('/email/verify', function () {
//     return view('auth.verify-email'); // 置いてある場所に合わせてパスを修正
// });
Route::middleware(['auth:web'])->group(function () {
    Route::get('/attendance', [UserAttendanceController::class, 'index']);
});
