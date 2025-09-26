<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;
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

        $query = $query
            ->orderBy('user_id', 'asc')
            ->orderBy('attendance_id', 'asc');

        $corrections = $query->get();

        return view('request-list', compact('tab', 'corrections'));
    }

    public function show(Request $request, $id)
    {
        $correction = Correction::findOrFile($id);
        $user = $correction->user;
        $attendance = $correction->attendance;
        $changes = $correction->changes;
        $date = $request->query('date', $attendance->work_date->toDateString());
        $workDate = Carbon::parse($date);
        $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get();
        $latestCorrection = $attendance->corrections()->latest()->first();

        return view('admin.approve.', compact('correction', 'user', 'attendance', 'changes', 'date', 'workDate', 'breakTimes', 'latestCorrection'));
    }
}
