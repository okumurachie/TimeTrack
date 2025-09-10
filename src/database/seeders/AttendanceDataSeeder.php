<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Correction;
use Carbon\Carbon;

class AttendanceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            for ($i = -1; $i <= 1; $i++) {
                $month = Carbon::now()->addMonthsNoOverflow($i);
                $start = Carbon::create($month->year, $month->month, 1);
                $end = Carbon::create($month->year, $month->month, $month->daysInMonth);

                $attendances = collect();
                for ($date = $start->copy(); $date->lessThanOrEqualTo($end); $date->addDay()) {
                    if ($date->isWeekday()) {
                        $attendance = Attendance::create([
                            'user_id' => $user->id,
                            'work_date' => $date,
                            'clock_in' => '09:00:00',
                            'clock_out' => '18:00:00',
                            'total_break' => 60,
                            'total_work' => 480,
                            'has_request' => false,
                        ]);

                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => '12:00:00',
                            'break_end' => '13:00:00',
                        ]);

                        $attendances->push($attendance);
                    }
                }

                foreach ($attendances->random(min(5, $attendances->count())) as $attendance) {
                    $statuses = ['pending', 'approved'];
                    $reasons = ['遅延のため', '早退のため', '打刻漏れのため'];

                    Correction::create([
                        'attendance_id' => $attendance->id,
                        'user_id' => $user->id,
                        'status' => $statuses[array_rand($statuses)],
                        'reason' => $reasons[array_rand($reasons)],
                    ]);

                    $attendance->update(['has_request' => true]);
                }
            }
        }
    }
}
