<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠登録</title>
</head>
<body>
    <h1>勤怠登録</h1>
    <div>
        <h2>{{ \Carbon\Carbon::now()->format('Y年m月d日 (D) H:i') }}</h2>
        
        @if ($attendance)
            @if ($attendance->status === 'off')
                <h3>勤務外</h3>
                <form action="{{ url('/attendance/check-in') }}" method="POST">
                    @csrf
                    <button type="submit" {{ $attendance->hasCheckedInToday() ? 'disabled' : '' }}>出勤</button>
                </form>
            @elseif ($attendance->status === 'working')
                <h3>出勤中</h3>
                <form action="{{ url('/attendance/check-out') }}" method="POST">
                    @csrf
                    <button type="submit">退勤</button>
                </form>
            @elseif ($attendance->status === 'checked_out')
                <h3>退勤済</h3>
                <p>お連れ様でした。</p>
            @endif
        @else
            <h3>勤怠情報が見つかりません。</h3>
            <form action="{{ url('/attendance/check-in') }}" method="POST">
                @csrf
                <button type="submit">出勤</button>
            </form>
        @endif
    </div>
</body>
</html>