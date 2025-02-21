<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ヘッダー</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>
    <header>
        <div class="logo">ロゴ</div>
        <nav>
            @if(Auth::check())
                <ul>
                    <li><a href="{{ url('/attendance') }}">勤怠</a></li>
                    <li><a href="{{ url('/attendance/list') }}">勤怠一覧</a></li>
                    <li><a href="{{ url('/stamp_correction_request/list') }}">申請</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit">ログアウト</button>
                        </form>
                    </li>
                </ul>
            @else
                <ul>
                    <li><a href="{{ url('/register') }}">ログイン</a></li>
                </ul>
            @endif
        </nav>
    </header>
</body>
</html>
