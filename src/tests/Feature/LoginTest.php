<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
public function email_is_required_on_login()
{
    // ユーザーを登録
    $this->post('/register', [
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    // メールアドレス以外の情報を入力してログイン
    $response = $this->post('/login', [
        'password' => 'password123',
    ]);

    // バリデーションメッセージを確認
    $response->assertSessionHasErrors('email');
    $this->assertEquals('メールアドレスを入力してください', $response->getSession()->get('errors')->get('email')[0]);
}

/** @test */
public function password_is_required_on_login()
{
    // ユーザーを登録
    $this->post('/register', [
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    // パスワード以外の情報を入力してログイン
    $response = $this->post('/login', [
        'email' => 'test@example.com',
    ]);

    // バリデーションメッセージを確認
    $response->assertSessionHasErrors('password');
    $this->assertEquals('パスワードを入力してください', $response->getSession()->get('errors')->get('password')[0]);
}

/** @test */
public function login_with_incorrect_credentials()
{
    // ユーザーを登録
    $this->post('/register', [
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    // 誤ったメールアドレスでログイン
    $response = $this->post('/login', [
        'email' => 'wrong@example.com',
        'password' => 'password123',
    ]);

    // バリデーションメッセージを確認
    $response->assertSessionHasErrors('email');
    $this->assertEquals('ログイン情報が登録されていません', $response->getSession()->get('errors')->get('email')[0]);
}

}
