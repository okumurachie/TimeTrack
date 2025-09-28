<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CorrectionRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Correction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();
        $date = $request->query('date', now()->toDateString());
        $workDate = Carbon::parse($date);
        $workDateLabel = Carbon::parse($date)->toJapaneseDate();

        $attendances = Attendance::whereDate('work_date', $workDate)
            ->get()
            ->keyBy('user_id');

        $hasAttendance = $attendances->isNotEmpty();
        return view('admin.index', compact('users', 'workDateLabel', 'workDate', 'attendances', 'hasAttendance'));
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

        return view('admin.staff-records', compact('user', 'dates', 'attendancesByDate', 'hasAttendance', 'currentMonth'));
    }

    public function detail(Request $request, Attendance $attendance)
    {
        $attendance->load(['user', 'corrections']);
        $user = $attendance->user;
        $date = $request->query('date', $attendance->work_date->toDateString());
        $workDate = Carbon::parse($date);
        $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get();
        $latestCorrection = $attendance->corrections()->latest()->first();

        return view('admin.detail', compact('user', 'attendance', 'workDate', 'breakTimes', 'latestCorrection'));
    }

    public function adminUpdate(CorrectionRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $attendance = Attendance::with('breakTimes')->findOrFail($id);

            $attendance->clock_in = $request->input('clock_in');
            $attendance->clock_out = $request->input('clock_out');

            $attendance->breakTimes()->delete();
            $date = $attendance->work_date->toDateString();

            foreach ($request->input('breaks', []) as $break) {
                if (!empty($break['start']) || !empty($break['end'])) {
                    $attendance->breakTimes()->create([
                        'break_start' => $date . ' ' . $break['start'] . ':00',
                        'break_end' => $date . ' ' . $break['end'] . ':00',
                    ]);
                }
            }

            $attendance->load('breakTimes');
            $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) :  null;

            $workMinutes = ($clockIn && $clockOut) ? $clockIn->diffInMinutes($clockOut) : 0;
            $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                $start = $break->break_start ? Carbon::parse($break->break_start) : null;
                $end = $break->break_end ? Carbon::parse($break->break_end) : null;
                return ($start && $end) ? $start->diffInMinutes($end) : 0;
            });

            $changes = [
                'clock_in' => $request->input('clock_in'),
                'clock_out' => $request->input('clock_out'),
                'breaks' => $request->input('breaks', []),
            ];

            $attendance->update([
                'clock_in'  => $clockIn,
                'clock_out' => $clockOut,
                'total_work' => max(0, $workMinutes - $breakMinutes),
                'total_break' => $breakMinutes,
                'has_request' => true,
            ]);

            Correction::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user->id,
                'admin_id' => auth('admin')->id(),
                'reason' => $request->input('reason'),
                'status' => 'approved',
                'changes' => $changes,
            ]);
        });
        return redirect()->route('admin.detail.record', $id);
    }
}
