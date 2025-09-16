<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        return view('attendance', compact('todayAttendance'));
    }

    public function stamp(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['is_on_break' => false],
        );

        $action = $request->input('action');

        switch ($action) {
            case 'clock_in';
                if (!$attendance->clock_in) {
                    $attendance->update(['clock_in' => now()]);
                    return redirect()->route('attendance.index');
                }
                break;

            case 'break_start';
                if (!$attendance->is_on_break) {
                    $attendance->update(['is_on_break' => true]);
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => now(),
                    ]);
                    return redirect()->route('attendance.index');
                }
                break;

            case 'break_end';
                if ($attendance->is_on_break) {
                    $attendance->update(['is_on_break' => false]);
                    $lastBreak = $attendance->breakTimes()->latest()->first();
                    if ($lastBreak && !$lastBreak->break_end) {
                        $lastBreak->update(['break_end' => now()]);
                    }
                    return redirect()->route('attendance.index');
                }
                break;

            case 'clock_out';
                if (!$attendance->clock_out) {
                    if ($attendance->is_on_break) {
                        $attendance->update(['is_on_break' => false]);
                        $lastBreak = $attendance->brakeTimes()->latest()->first();
                        if ($lastBreak && !$lastBreak->break_end) {
                            $lastBreak->update(['break_end' => now()]);
                        }
                    }

                    $attendance->update(['clock_out' => now()]);

                    $workMinutes = $attendance->clock_in->diffInMinutes($attendance->clock_out);

                    $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                        return $break->break_end ? $break->break_start->diffInMinutes($break->break_end) : 0;
                    });

                    $attendance->total_work = $workMinutes - $breakMinutes;
                    $attendance->total_break = $breakMinutes;
                    $attendance->save();

                    return redirect()->route('attendance.index');
                }
                break;

            default:
                return redirect()->route('attendance.index')->with('error', '無効な操作です');
        }
        return redirect()->route('attendance.index')->with('error', 'すでに打刻済みです');
    }

    public function showMyRecord(Request $request)
    {
        $user = Auth::user();

        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFormFormat('Y-m', $month);

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date')
            ->get();

        return view('my-record', compact('attendances', 'currentMonth'));
    }
}
