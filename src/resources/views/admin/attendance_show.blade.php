@extends('layouts.adminheader')

@section('content')
<div class="container">
    <h1>勤怠詳細 (管理者)</h1>
    
    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">名前</label>
            <p>{{ $attendance->user->name }}</p>
        </div>

        <div class="form-group">
            <label for="date">日付</label>
            <p>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</p>
        </div>

        <div class="form-group">
    <label for="check_in">出勤</label>
    <input type="time" name="check_in" value="{{ old('check_in', $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '') }}" class="form-control">
    @error('check_in')
        <div class="text-danger">{{ $message }}</div>
    @enderror

    <label for="check_out">退勤</label>
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
                <input type="time" name="breaks[{{ $index }}][start]" value="{{ old("breaks.$index.start", $break->start ? $break->start->format('H:i') : '00:00') }}" class="form-control">
                @error("breaks.$index.start")
                    <div class="text-danger">{{ $message }}</div>
                @enderror
                <label>休憩終了時間</label>
                <input type="time" name="breaks[{{ $index }}][end]" value="{{ old("breaks.$index.end", $break->end ? $break->end->format('H:i') : '00:00') }}" class="form-control">
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
            <textarea name="remarks" class="form-control" required>{{ old('remarks', $attendance->remarks) }}</textarea>
            @error('remarks')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">修正</button>
    </form>

    <hr>

    <!-- 承認・拒否ボタン -->
    <h2>申請の承認/拒否</h2>
    <form action="{{ route('admin.attendance.approve', $attendance->id) }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-success">承認</button>
    </form>

    <form action="{{ route('admin.attendance.reject', $attendance->id) }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-danger">拒否</button>
    </form>

    <!-- メッセージ表示 -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection