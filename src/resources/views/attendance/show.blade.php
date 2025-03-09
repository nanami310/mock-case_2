@extends('layouts.header')

@section('content')
<div class="container">
    <h1>勤怠詳細</h1>
    
    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">名前</label>
            <input type="text" id="name" class="form-control" value="{{ auth()->user()->name }}" readonly>
        </div>

        <div class="form-group">
            <label for="date">日付</label>
            <input type="text" id="date" class="form-control" value="{{ $attendance->date->format('Y年n月j日') }}" readonly>
        </div>

        <div class="form-group">
            <label for="check_in">出勤</label>
            <input type="time" name="check_in" value="{{ $attendance->check_in->format('H:i') }}" class="form-control" required {{ 
            $attendance->status === 'pending' ? 'disabled' : '' }}>
            @error('check_in')
                <div class="text-danger">{{ $message }}</div>
            @enderror

            <label for="check_out">退勤</label>
            <input type="time" name="check_out" value="{{ $attendance->check_out->format('H:i') }}" class="form-control" required {{ 
            $attendance->status === 'pending' ? 'disabled' : '' }}>
            @error('check_out')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
    <label for="break_time">休憩</label>
    @if($breakTimes->isNotEmpty())
        @foreach ($breakTimes as $index => $break)
            <input type="time" name="breaks[{{ $index }}][start]" value="{{ $break->start ? $break->start->format('H:i') : '00:00' }}" 
class="form-control" required {{ 
            $attendance->status === 'pending' ? 'disabled' : '' }}>
            @error("breaks.$index.start")
                <div class="text-danger">{{ $message }}</div>
            @enderror

            <input type="time" name="breaks[{{ $index }}][end]" value="{{ $break->end ? $break->end->format('H:i') : '00:00' }}" 
class="form-control" required {{ 
            $attendance->status === 'pending' ? 'disabled' : '' }}>
            @error("breaks.$index.end")
                <div class="text-danger">{{ $message }}</div>
            @enderror
        @endforeach
    @else
        <p>勤怠情報はありません</p>
    @endif
</div>




        <div class="form-group">
            <label for="remarks">備考</label>
            <textarea name="remarks" class="form-control" required {{ $attendance->status === 'pending' ? 'disabled' : '' }}>{{ 
            $attendance->remarks }}</textarea>
            @error('remarks')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        @if($attendance->status !== 'pending')
            <button type="submit" class="btn btn-primary">修正</button>
        @else
            <p>*承認待ちのため修正はできません。</p>
        @endif
    </form>
</div>
@endsection
