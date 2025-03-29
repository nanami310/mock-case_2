@extends('layouts.adminheader')

@section('content')
<div class="container">
    <h1>修正申請承認画面</h1>
    
    <div class="form-group">
        <label for="name">名前</label>
        <p>{{ $attendanceStatus->user->name }}</p>
    </div>

    <div class="form-group">
        <label for="date">日付</label>
        <p>{{ \Carbon\Carbon::parse($attendanceStatus->attendance->date)->format('Y年n月j日') }}</p>
    </div>

    <div class="form-group">
        <label for="check_in">出勤</label>
        <p>{{ \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->check_in)->format('H:i') }}</p>
    </div>

    <div class="form-group">
        <label for="check_out">退勤</label>
        <p>{{ \Carbon\Carbon::createFromFormat('H:i:s', $attendanceStatus->check_out)->format('H:i') }}</p>
    </div>

<div class="form-group">
    <label for="break_time">休憩時間</label>
    @if($attendanceStatus->break_start || $attendanceStatus->break_end)
        <div class="form-group">
            <label>休憩開始時間</label>
            <p>{{ $attendanceStatus->break_start ? \Carbon\Carbon::parse($attendanceStatus->break_start)->format('H:i') : '未設定' }}</p>
            <label>休憩終了時間</label>
            <p>{{ $attendanceStatus->break_end ? \Carbon\Carbon::parse($attendanceStatus->break_end)->format('H:i') : '未設定' }}</p>
        </div>
    @else
        <p>休憩情報はありません</p>
    @endif
</div>

    <div class="form-group">
        <label for="remarks">備考</label>
        <p>{{ $attendanceStatus->remarks }}</p>
    </div>

    @if($attendanceStatus->status === 'approved')
        <button class="btn btn-success" disabled>承認済み</button>
    @else
        <form action="{{ route('admin.attendance.approve', $attendanceStatus->attendance_id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-success">承認</button>
        </form>
    @endif

    <form action="{{ route('admin.attendance.reject', $attendanceStatus->attendance_id) }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-danger">拒否</button>
    </form>
</div>
@endsection
