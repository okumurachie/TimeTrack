<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->intended('/admin/attendance/list');
        }

        if (Auth::guard('web')->check()) {
            return redirect()->intended('/attendance');
        }
    }
}
