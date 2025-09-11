<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifySessionController;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticatedSessionController extends FortifySessionController
{
    protected function guard()
    {
        return Auth::guard('admin');
    }
}
