<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Correction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CorrectionController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');

        if (Auth::guard('admin')->check()) {
            $query = Correction::with(['user', 'attendance']);
        } else {
            $query = Correction::with(['user', 'attendance'])
                ->where('user_id', Auth::id());
        }

        if ($tab === 'pending') {
            $query->where('status', 'pending');
        } elseif ($tab === 'approved') {
            $query->where('status', 'approved');
        }

        $corrections = $query->latest()->get();

        return view('request-list', compact('tab', 'corrections'));
    }
}
