@extends('layouts.header')

@section('content')
<div class="container">
    <h1>勤怠詳細</h1>
    
    <form action="{{ route('attendance.requestChange', $attendance->id) }}" method="POST">
        @csrf
        
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        
        <div class="form-group">
            <label for="name">名前</label>
            <input type="text" id="name" class="form-control" value="{{ auth()->user()->name }}" readonly>
        </div>

        <div class="form-group">
    <label for="date">日付</label>
    <input type="text" id="date" class="form-control" value="{{ \Carbon\Carbon::parse($attendance->created_at)->format('Y年n月j日') }}" readonly>
</div>

<div class="form-group">
    <label for="check_in">新しい出勤時間</label>
    <input type="time" name="check_in" value="{{ old('check_in', $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '') }}" class="form-control">
    @error('check_in')
        <div class="text-danger">{{ $message }}</div>
    @enderror

    <label for="check_out">新しい退勤時間</label>
    <input type="time" name="check_out" value="{{ old('check_out', $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '') }}" class="form-control">
    @error('check_out')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="break_time">休憩時間</label>
    @if($breakTimes->isNotEmpty())
        @foreach ($breakTimes as $index => $break)
            <div class="form-group">
                <label>休憩開始時間</label>
                <input type="time" name="breaks[{{ $index }}][start]" 
                       value="{{ old("breaks.$index.start", $break->start ? $break->start->format('H:i') : '') }}" 
                       class="form-control">
                @error("breaks.$index.start")
                    <div class="text-danger">{{ $message }}</div>
                @enderror

                <label>休憩終了時間</label>
                <input type="time" name="breaks[{{ $index }}][end]" 
                       value="{{ old("breaks.$index.end", $break->end ? $break->end->format('H:i') : '') }}" 
                       class="form-control">
                @error("breaks.$index.end")
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        @endforeach
    @else
        <p>勤怠情報はありません</p>
    @endif
</div>

        <div class="form-group">
            <label for="remarks">備考</label>
            <textarea name="remarks" class="form-control">{{ old('remarks') }}</textarea>
            @error('remarks')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        @php
    $attendanceStatus = $attendance->attendanceStatus; // 修正
@endphp

@if(!$attendanceStatus || $attendanceStatus->status !== 'pending')
    <button type="submit" class="btn btn-primary">変更申請</button>
@elseif($attendanceStatus && $attendanceStatus->status === 'pending') // 修正
    <p class="text-danger">*承認待ちのため修正はできません。</p>
@endif
    </form>

    <div id="message" class="alert alert-info" style="display: none; margin-top: 20px;">
        修正内容が保存されました。
    </div>
</div>

@endsection
