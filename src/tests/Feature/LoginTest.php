<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    public function test_login_user_validate_email()
    {
        User::create([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('abcd1234'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'abcd1234',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);

        $response = $this->get('/login');
        $response->assertSee('メールアドレスを入力してください');
    }

    public function test_login_user_validate_password()
    {
        User::create([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('abcd1234'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);

        $response = $this->get('/login');
        $response->assertSee('パスワードを入力してください');
    }

    public function test_login_user_validate_user()
    {
        User::create([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('abcd1234'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test1@example.com',
            'password' => 'abcd1234',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);

        $response = $this->get('/login');
        $response->assertSee('ログイン情報が登録されていません');
    }
}
