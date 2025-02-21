<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;

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

Route::get('/register', function () {
    return view('register'); // 会員登録画面のビューを表示
});

Route::post('/register', [AuthController::class, 'register']);
// ログインルート
Route::get('/login', [AuthController::class, 'loginForm'])->name('login'); // ログインフォームを表示するルート
Route::post('/login', [AuthController::class, 'login']); // ログイン処理を行うルート

Route::get('/attendance', [AttendanceController::class, 'index'])->middleware('auth');
Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->middleware('auth');
Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->middleware('auth');
Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak'])->middleware('auth');
Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak'])->middleware('auth');