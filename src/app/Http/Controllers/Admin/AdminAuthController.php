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
    // 年、月、日をリクエストから取得（デフォルトは現在の日付）
    $currentYear = $request->input('year', date('Y'));
    $currentMonth = $request->input('month', date('m'));
    $currentDay = $request->input('day', date('d'));

    // 選択された日付を作成
    $selectedDate = \Carbon\Carbon::createFromFormat('Y-m-d', "$currentYear-$currentMonth-$currentDay");

    // 日ごとの勤怠情報を取得
    $attendanceRecords = Attendance::with('user')
        ->whereDate('date', $selectedDate)
        ->get();

    return view('admin.attendance_list', compact('attendanceRecords', 'currentYear', 'currentMonth', 'currentDay'));
}


    // ログアウト処理
public function logout()
{
    Auth::logout();
    return redirect()->route('admin.login'); // ここを修正
}
}