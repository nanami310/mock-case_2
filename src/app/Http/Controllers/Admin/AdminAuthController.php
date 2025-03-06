<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class AdminAuthController extends Controller
{
    // ログインフォームを表示
    public function showLoginForm()
    {
        return view('admin.login');
    }

    // ログイン処理
    public function login(AdminLoginRequest $request)
    {

        // 認証
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->route('admin.attendance.list'); // 勤怠一覧画面へリダイレクト
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。',
        ]);
    }

    // 管理者用勤怠一覧画面
    public function adminAttendanceIndex(Request $request)
    {
        $currentYear = $request->input('year', date('Y'));
        $currentMonth = $request->input('month', date('m'));

        $attendanceRecords = Attendance::with('user')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->get();

        return view('admin.attendance_list', compact('attendanceRecords', 'currentYear', 'currentMonth'));
    }

    // ログアウト処理
public function logout()
{
    Auth::logout();
    return redirect()->route('admin.login'); // ここを修正
}
}