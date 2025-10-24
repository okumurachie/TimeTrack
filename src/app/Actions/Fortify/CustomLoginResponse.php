<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;


class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->intended(route('admin.attendances.index'));
        }

        if (Auth::guard('web')->check()) {
            return redirect()->intended(route('attendance.index'));
        }
    }
}
