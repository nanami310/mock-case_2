<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function rules()
    {
        return [
            'attendance_id' => 'required|exists:attendances,id', // attendance_id のバリデーションを追加
            'check_in' => 'required|date_format:H:i|before:check_out',
            'check_out' => 'required|date_format:H:i|after:check_in',
            'breaks.*.start' => 'required|date_format:H:i|before:breaks.*.end|after:check_in|before:check_out',
            'breaks.*.end' => 'required|date_format:H:i|after:breaks.*.start|after:check_in|before:check_out',
            'remarks' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'attendance_id.required' => '勤怠情報が必要です', // attendance_id に対するエラーメッセージを追加
            'attendance_id.exists' => '指定された勤怠情報が存在しません',
            'check_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'check_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.start.before' => '休憩時間が勤務時間外です',
            'breaks.*.end.after' => '休憩時間が勤務時間外です',
            'breaks.*.end.before' => '休憩時間が勤務時間外です',
            'remarks.required' => '備考を記入してください',
        ];
    }
}
