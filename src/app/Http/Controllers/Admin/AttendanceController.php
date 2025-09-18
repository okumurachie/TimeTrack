<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();
        $today = Carbon::now()->toJapaneseDate();
        $date = $request->query('date', now()->toDateString());
        $workDate = Carbon::parse($date);

        $attendances = Attendance::whereDate('work_date', $workDate)
            ->get()
            ->keyBy('user_id');

        $hasAttendance = $attendances->isNotEmpty();
        return view('admin.index', compact('users', 'today', 'workDate', 'attendances', 'hasAttendance'));
    }

    public function staffList(Request $request)
    {
        $users = User::all();
        return view('admin.staff-list', compact('users'));
    }

    public function showStaffRecord(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->query('month', Carbon::now()->format('Y-m'));
        try {
            $currentMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = Carbon::now()->startOfMonth();
        }

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date')
            ->get();

        $hasAttendance = $attendances->isNotEmpty();
        $attendancesByDate = $attendances->keyBy(fn($attendance) => $attendance->work_date->format('Y-m-d'));

        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        return view('admin/staff-records', compact('user', 'dates', 'attendancesByDate', 'hasAttendance', 'currentMonth'));
    }
}
