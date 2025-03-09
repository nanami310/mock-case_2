@extends('layouts.adminheader')

@section('content')
<div class="container">
    <h1>勤怠詳細 (管理者)</h1>
    
    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
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
            <input type="time" name="check_in" value="{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '' }}" class="form-control" required>
            @error('check_in')
                <div class="text-danger">{{ $message }}</div>
            @enderror

            <label for="check_out">退勤</label>
            <input type="time" name="check_out" value="{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '' }}" class="form-control" required>
            @error('check_out')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
    <label for="break_time">休憩</label>
    @if($attendance->breaks && $attendance->breaks->isNotEmpty())
        @foreach ($attendance->breaks as $index => $break)
            <label for="breaks[{{ $index }}][start]">休憩{{ $index + 1 }}</label>
            <input type="time" name="breaks[{{ $index }}][start]" value="{{ $break->start ? \Carbon\Carbon::parse($break->start)->format('H:i') : '00:00' }}" class="form-control" required>
            @error("breaks.$index.start")
                <div class="text-danger">{{ $message }}</div>
            @enderror
            <label for="breaks[{{ $index }}][end]">～</label>
            <input type="time" name="breaks[{{ $index }}][end]" value="{{ $break->end ? \Carbon\Carbon::parse($break->end)->format('H:i') : '00:00' }}" class="form-control" required>
            @error("breaks.$index.end")
                <div class="text-danger">{{ $message }}</div>
            @enderror
        @endforeach
    @else
        <label for="breaks[0][start]">休憩1</label>
        <input type="time" name="breaks[0][start]" value="00:00" class="form-control" required>
        <label for="breaks[0][end]">～</label>
        <input type="time" name="breaks[0][end]" value="00:00" class="form-control" required>
    @endif
</div>




        <div class="form-group">
            <label for="remarks">備考</label>
            <textarea name="remarks" class="form-control" required>{{ $attendance->remarks }}</textarea>
            @error('remarks')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">修正</button>
    </form>
</div>
@endsection