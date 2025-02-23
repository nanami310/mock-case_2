@extends('layouts.header')
@section('content')
    <div>
        <h1>会員登録</h1>
        <form action="{{ url('/register') }}" method="POST">
            @csrf
            <div>
                <label for="name">名前</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}">
                @error('name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password">
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="password_confirmation">パスワード確認</label>
                <input type="password" id="password_confirmation" name="password_confirmation">
            </div>
            <button type="submit">登録する</button>
        </form>
        <p><a href="{{ url('/login') }}">ログインはこちら</a></p>
</div>
@endsection