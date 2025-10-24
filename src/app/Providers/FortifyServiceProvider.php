<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\CustomRegisterResponse;
use App\Actions\Fortify\CustomLoginResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use App\Http\Responses\LogoutResponse;
use App\Models\User;
use App\Models\Admin;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RegisterResponse::class, CustomRegisterResponse::class);
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (FortifyLoginRequest $request) {

            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);

            if ($request->is('admin/*')) {
                $admin = Admin::where('email', $request->email)->first();
                if ($admin && Hash::check($request->password, $admin->password)) {
                    Auth::guard('admin')->login($admin);
                    return $admin;
                }
            } else {
                $user = User::where('email', $request->email)->first();
                if ($user && Hash::check($request->password, $user->password)) {
                    Auth::guard('web')->login($user);
                    return $user;
                }
            }
            return null;
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            if (request()->is('admin/login')) {
                return view('admin.login');
            }
            return view('auth.login');
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });


        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }
}
