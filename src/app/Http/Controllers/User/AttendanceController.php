<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('attendance');
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
                    $attendance->update(['clock_in' => now()->format('H:i:s')]);
                    return back()->with('success', '出勤打刻しました');
                }
                break;

            case 'break_start';
                if (!$attendance->is_on_break) {
                    $attendance->update(['is_on_break' => true]);
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => now()->format('H:i:s'),
                    ]);
                    return back()->with('success', '休憩開始しました');
                }
                break;

            case 'break_end';
                if ($attendance->is_on_break) {
                    $attendance->update(['is_on_break' => false]);
                    $lastBreak = $attendance->breakTimes()->latest()->first();
                    if ($lastBreak && !$lastBreak->break_end) {
                        $lastBreak->update(['break_end' => now()->format('H:i:s')]);
                    }
                    return back()->with('success', '休憩終了しました');
                }
                break;

            case 'clock_out';
                if (!$attendance->clock_out) {
                    if ($attendance->is_on_break) {
                        $attendance->update(['is_on_break' => false]);
                        $lastBreak = $attendance->brakeTimes()->latest()->first();
                        if ($lastBreak && !$lastBreak->break_end) {
                            $lastBreak->update(['break_end' => now()->format('H:i:s')]);
                        }
                    }

                    $attendance->update(['clock_out' => now()->format('H:i:s')]);

                    $workMinutes = $attendance->clock_in->diffInMinutes($attendance->clock_out);

                    $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                        return $break->break_end ? $break->break_start->diffInMinutes($break->break_end) : 0;
                    });

                    $attendance->total_work = $workMinutes - $breakMinutes;
                    $attendance->total_break = $breakMinutes;
                    $attendance->save();

                    return back()->with('success', '退勤打刻しました');
                }
                break;

            default:
                return back()->with('error', '無効な操作です');
        }
        return back()->with('error', 'すでに打刻済みです');
    }
}
