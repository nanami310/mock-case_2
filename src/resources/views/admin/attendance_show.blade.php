@extends('layouts.adminheader')

@section('content')
<div class="container">
    <h1>勤怠詳細</h1>
    
    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">名前</label>
            <p>{{ $attendance->user->name ?? '未設定' }}</p>
        </div>

        <div class="form-group">
            <label for="date">日付</label>
            <p>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</p>
        </div>

        <div class="form-group">
            <label for="check_in">出勤・退勤</label>
            <input type="time" name="check_in" value="{{ old('check_in', $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '') }}" class="form-control">～
            <input type="time" name="check_out" value="{{ old('check_out', $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '') }}" class="form-control">
            @error('check_in')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>休憩</label>
            @if($breakTimes->isNotEmpty())
                @foreach ($breakTimes as $index => $break)
                    <div class="form-group">
                        <input type="time" name="breaks[{{ $index }}][start]" value="{{ old("breaks.$index.start", $break->start ? $break->start->format('H:i') : '') }}" class="form-control">～
                        <input type="time" name="breaks[{{ $index }}][end]" value="{{ old("breaks.$index.end", $break->end ? $break->end->format('H:i') : '') }}" class="form-control">
                        @error("breaks.$index.start")
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            @else
                <div class="form-group">
                    <input type="time" name="breaks[0][start]" value="" class="form-control">～
                    <input type="time" name="breaks[0][end]" value="" class="form-control">
                </div>
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
</div>
@endsection
