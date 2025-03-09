<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AdminAttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('user')->get(); // ユーザー情報を含めて全ての勤怠を取得
        return view('admin.attendance_list', compact('attendances'));
    }

    // 勤怠詳細画面を表示
    public function show($id)
{
    $attendance = Attendance::with('breaks')->findOrFail($id); // breaks リレーションを含めて取得
    return view('admin.attendance_show', compact('attendance'));
}

    public function update(Request $request, $id)
    {
        $request->validate([
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'breaks.*.start' => 'nullable|date_format:H:i', // 修正: 配列形式に対応
            'breaks.*.end' => 'nullable|date_format:H:i',
            'remarks' => 'nullable|string|max:255',
        ]);

        $attendance = Attendance::findOrFail($id);

        // データを更新
        $attendance->check_in = $request->input('check_in');
        $attendance->check_out = $request->input('check_out');

        // 日付を取得 (例: 2025-03-09)
        $date = $attendance->date; // もしくは適切な日付を指定する

        // 休憩データの更新処理
        if ($request->has('breaks') && is_array($request->input('breaks'))) {
            $attendance->breaks()->delete(); // 既存の休憩データを削除
            foreach ($request->input('breaks') as $break) {
                // DATETIMEフォーマットに変換
                $start = $date . ' ' . $break['start'] . ':00'; // 秒を追加
                $end = $date . ' ' . $break['end'] . ':00'; // 秒を追加
                
                // 休憩時間を作成
                $attendance->breaks()->create([
                    'start' => $start,
                    'end' => $end,
                ]);
            }
        }

        $attendance->remarks = $request->input('remarks');

        $attendance->save(); // 保存

        // 勤怠一覧画面にリダイレクト
        return redirect()->route('attendance.list')->with('success', '勤怠情報が更新されました。');
    }
}
