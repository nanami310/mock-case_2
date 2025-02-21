<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => '正しいメールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }

    public function authorize()
    {
        return true; // 認可を常に許可
    }
}
