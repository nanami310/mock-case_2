<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者用ヘッダー</title>
</head>
<body>
    <div class="header">
        <div class="logo">ロゴ</div>
        <div class="nav-buttons">
            @if(Auth::check())
                <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
                <a href="/admin/staff/list">スタッフ一覧</a>
                <a href="{{ route('admin.attendance.requests') }}" class="btn btn-info">申請一覧</a>
                <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:blue; cursor:pointer;">ログアウト</button>
                </form>
            @else
                <a href="/admin/login">ログイン</a>
            @endif
        </div>
    </div>
    @yield('content')
</body>
</html>