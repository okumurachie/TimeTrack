<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\AdminsTableSeeder;

use Tests\TestCase;
use App\Models\Admin;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminsTableSeeder::class);
    }

    public function test_login_admin_validate_email()
    {
        $admin = Admin::find(1);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => '',
            'password' =>  Hash::make($admin['password']),
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);

        $response = $this->get('/admin/login');
        $response->assertSee('メールアドレスを入力してください');
    }

    public function test_login_admin_validate_password()
    {
        $admin = Admin::find(1);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => $admin['email'],
            'password' => '',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);

        $response = $this->get('/admin/login');
        $response->assertSee('パスワードを入力してください');
    }

    public function test_login_admin_validate_admin()
    {
        $admin = Admin::find(1);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin3@coachtech.com',
            'password' =>  Hash::make($admin['password']),
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);

        $response = $this->get('/admin/login');
        $response->assertSee('ログイン情報が登録されていません');
    }
}
