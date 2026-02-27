<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 1-1: 名前が入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_register_validation_name_required()
    {
        $response = $this->post('/register', [
            'name' => '', // 空にする
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']); // nameにエラーがあるか確認
    }

    /**
     * ID 1-2: メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_register_validation_email_required()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => '', // 空にする
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * ID 1-3: パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_register_validation_password_required()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '', // 空にする
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * ID 1-4: パスワードが7文字以下の場合、バリデーションメッセージが表示される
     */
    public function test_register_validation_password_min_length()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '1234567', // 7文字
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * ID 1-5: パスワードが確認用パスワードと一致しない場合、バリデーションメッセージが表示される
     */
    public function test_register_validation_password_confirmation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123', // 違うパスワード
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * ID 1-6: 全ての項目が入力されている場合、会員情報が登録され、プロフィール設定画面（メール認証）に遷移される
     * ※現在の仕様に合わせて /email/verify への遷移を確認します
     */
    public function test_register_success()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'success@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $this->assertAuthenticated(); // ログイン状態になっているか

        // 直前の実装でメール認証画面への遷移にしたため、ここを確認
        $response->assertRedirect('/email/verify');
    }

   // ==========================================
    // ID 2: ログイン機能
    // ==========================================

    /**
     * ID 2-1: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_login_validation_email_required()
    {
        $response = $this->post('/login', [
            'email' => '', // 未入力
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * ID 2-2: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_login_validation_password_required()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '', // 未入力
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * ID 2-3: 入力情報が間違っている場合、バリデーションメッセージが表示される
     */
    public function test_login_failed_with_invalid_credentials()
    {
        // 存在しないユーザー情報でログインを試みる
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        // 認証失敗時は通常 'email' フィールドにエラーが返されます
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * ID 2-4: 正しい情報が入力された場合、ログイン処理が実行される
     */
    public function test_login_success()
    {
        // テスト用のユーザーを作成
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user); // ログインできているか
        $response->assertRedirect('/');      // 商品一覧（トップページ）へリダイレクトされるか
    }

    // ==========================================
    // ID 3: ログアウト機能
    // ==========================================

    /**
     * ID 3-1: ログアウトができる
     */
    public function test_logout_success()
    {
        // ユーザーを作成してログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // ログアウト処理を実行（POST /logout）
        $response = $this->post('/logout');

        // 未ログイン状態（ゲスト）になっていることを確認
        $this->assertGuest();

        // ログアウト後のリダイレクト先を確認（通常はトップページ '/'）
        $response->assertRedirect('/');
    }
}