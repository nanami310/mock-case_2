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
Route::get('/', function () {
    return view('welcome'); // または適切なビュー
});


Route::get('/register', function () {
    return view('register'); // 会員登録画面のビューを表示
});

Route::post('/register', [AuthController::class, 'register']);
// ログインルート
Route::get('/login', [AuthController::class, 'loginForm'])->name('login'); // ログインフォームを表示するルート
Route::post('/login', [AuthController::class, 'login']); // ログイン処理を行うルート
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/attendance', [AttendanceController::class, 'index'])->middleware('auth');
Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->middleware('auth');
Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->middleware('auth');
Route::post('/attendance/take-break', [AttendanceController::class, 'takeBreak']);
Route::post('/attendance/return-from-break', [AttendanceController::class, 'returnFromBreak']);

Route::get('/attendance/list', [AttendanceController::class, 'attendanceList'])->name('attendance.list');
Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');