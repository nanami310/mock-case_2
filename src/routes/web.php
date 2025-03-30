<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
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

// ユーザー用ルート
Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', function () {
    return view('register');
});

Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/attendance', [AttendanceController::class, 'index'])->middleware('auth');
Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->middleware('auth');
Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->middleware('auth');
Route::post('/attendance/take-break', [AttendanceController::class, 'takeBreak']);
Route::post('/attendance/return-from-break', [AttendanceController::class, 'returnFromBreak']);

Route::get('/attendance/list', [AttendanceController::class, 'attendanceList'])->name('attendance.list');
Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

Route::post('/attendance/request-change/{id}', [AttendanceController::class, 'requestChange'])->name('attendance.requestChange');

Route::get('/attendance/approvals', [AttendanceController::class, 'approvals'])->name('attendance.approvals');

// 一般ユーザー用ルート
Route::middleware(['auth'])->group(function () {
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('user.attendance.requests'); // 新しい名前
});

// 管理者用ルート
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'adminAttendanceIndex'])->name('admin.attendance.list')->middleware('auth');

// 名前を変更
Route::put('/admin/attendance/update/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
Route::post('/admin/attendance/{id}/update', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update.individual'); // 名前を変更

Route::get('/admin/attendance', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');

Route::get('/admin/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');
Route::get('/admin/attendance/staff/{id}', [StaffController::class, 'attendance'])->name('admin.attendance.staff');
Route::post('/admin/attendance/staff/{id}/export', [StaffController::class, 'exportCsv'])->name('admin.attendance.export');

Route::post('/admin/attendance/{id}/approve', [AdminAttendanceController::class, 'approve'])->name('admin.attendance.approve');
Route::post('/admin/attendance/{id}/reject', [AdminAttendanceController::class, 'reject'])->name('admin.attendance.reject');


Route::middleware(['auth'])->group(function () {
    Route::get('/admin/stamp_correction_request/list', [AdminAttendanceController::class, 'listRequests'])->name('admin.attendance.requests');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminAttendanceController::class, 'approve'])->name('admin.
attendance.approve');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/attendance', [AdminAttendanceController::class, 'adminAttendanceIndex'])->name('admin.attendance.index');
});

// 修正申請承認画面
Route::get('/attendance/correction/{id}/approve', [AdminAttendanceController::class, 'showCorrectionRequest'])->name('admin.attendance.correction.approve');