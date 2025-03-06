<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    // ユーザーがこのリクエストを送信できるかどうかを判断
    public function authorize()
    {
        return true; // 認可を許可
    }

    // バリデーションルール
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    // バリデーションエラーメッセージ
    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'email.email' => '正しいメールアドレスを入力してください。',
        ];
    }
}
