<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'デフォルトタイトル')</title>
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
                    <li><a href="{{ route('user.attendance.requests') }}" class="btn btn-info">申請</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit">ログアウト</button>
                        </form>
                    </li>
                </ul>
            @else
            @endif
        </nav>
    </header>

    <div class="container">
        @yield('content') <!-- ここに子ビューのコンテンツが挿入される -->
    </div>
</body>
</html>