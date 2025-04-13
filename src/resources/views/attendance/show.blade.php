@extends('layouts.header')

@section('content')
<div class="container">
    <h1>勤怠詳細</h1>
    <form action="{{ route('attendance.requestChange2') }}" method="POST">
        @csrf

        @if(isset($attendance))
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        @else
            <input type="hidden" name="attendance_id" value="">
        @endif

        <div class="form-group">
            <label for="name">名前</label>
            <input type="text" id="name" class="form-control" value="{{ auth()->user()->name }}" readonly>
        </div>

        <div class="form-group">
            <label for="date">日付</label>
            <input type="text" id="date" class="form-control" value="{{ $formattedDate }}" readonly>
        </div>

        <div class="form-group">
            <label for="check_in">出勤</label>
            <input type="time" name="check_in" value="{{ old('check_in', $attendance->check_in ? $attendance->check_in->format('H:i') : '') }}" class="form-control">
            @error('check_in')
                <div class="text-danger">{{ $message }}</div>
            @enderror

            <label for="check_out">退勤</label>
            <input type="time" name="check_out" value="{{ old('check_out', $attendance->check_out ? $attendance->check_out->format('H:i') : '') }}" class="form-control">
            @error('check_out')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="break_time">休憩時間</label>
            <div id="breaks">
                @if(isset($breaks) && $breaks->isNotEmpty())
                    @foreach ($breaks as $index => $break)
                        <div class="form-group">
                            <input type="time" name="breaks[{{ $index }}][start]" value="{{ old("breaks.$index.start", $break->start ? $break->start->format('H:i') : '') }}" class="form-control" placeholder="休憩開始">
                            @error("breaks.$index.start")
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <input type="time" name="breaks[{{ $index }}][end]" value="{{ old("breaks.$index.end", $break->end ? $break->end->format('H:i') : '') }}" class="form-control" placeholder="休憩終了">
                            @error("breaks.$index.end")
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                @else
                    <div class="form-group">
                        <input type="time" name="breaks[0][start]" class="form-control" placeholder="休憩開始">
                        <input type="time" name="breaks[0][end]" class="form-control" placeholder="休憩終了">
                    </div>
                @endif
            </div>
        </div>

        <div class="form-group">
            <label for="remarks">備考</label>
            <textarea name="remarks" class="form-control">{{ old('remarks', $attendance->remarks ?? '') }}</textarea>
            @error('remarks')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        @if(isset($attendanceStatus) && $attendanceStatus->status === 'pending')
            <p class="text-warning">申請中です</p>
        @else
            <button type="submit" class="btn btn-primary">修正</button>
        @endif
    </form>

    <div id="message" class="alert alert-info" style="display: none; margin-top: 20px;">
        修正内容が保存されました。
    </div>
</div>

<script>
function addBreak() {
    const breaksDiv = document.getElementById('breaks');
    const index = breaksDiv.children.length;

    const breakHtml = `
        <div class="form-group">
            <input type="time" name="breaks[${index}][start]" class="form-control" placeholder="休憩開始">
            <input type="time" name="breaks[${index}][end]" class="form-control" placeholder="休憩終了">
        </div>
    `;
    breaksDiv.insertAdjacentHTML('beforeend', breakHtml);
}
</script>
@endsection
