<?php

namespace App\Actions\Fortify;


use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Http\RedirectResponse;

class CustomRegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        return redirect('/email/verify');
    }
}
