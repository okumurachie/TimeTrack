<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AdminsTableSeeder;
use Database\Seeders\AttendanceDataSeeder;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('config:clear');

        $this->seed(AdminsTableSeeder::class);
        $this->seed(UsersTableSeeder::class);
        $this->seed(AttendanceDataSeeder::class);
    }

    public function test_admin_can_view_all_users_attendance_for_the_day()
    {
        $admin = Admin::find(1);
        $date = Carbon::today()->toDateString();
        $workDate = Carbon::parse($date);
        $workDateLabel = Carbon::parse($date)->toJapaneseDate();

        $attendances = Attendance::where('work_date', $workDate)
            ->get()
            ->keyBy('user_id');

        $hasAttendance = $attendances->isNotEmpty();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendances.index'));
        $response->assertStatus(200);
        $response->assertSee("{$workDateLabel}の勤怠");

        if (!$hasAttendance) {
            $response->assertSee('この日の勤怠データはありません');
        } else {
            foreach ($attendances as $attendance) {
                $response->assertSee($attendance->user->name);
                $response->assertSee($attendance?->clock_in?->format('H:i') ?? '');
                $response->assertSee($attendance?->clock_out?->format('H:i') ?? '');
                $response->assertSee($attendance?->total_break ? gmdate('H:i', $attendance->total_break * 60) : '');
                $response->assertSee($attendance?->total_break ? gmdate('H:i', $attendance->total_work * 60) : '');
            }
        }
    }

    public function test_admin_attendance_list_displays_today_date()
    {
        $admin = Admin::find(1);
        $date = Carbon::today()->toDateString();
        $workDate = Carbon::parse($date);
        $workDateLabel = Carbon::parse($date)->toJapaneseDate();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendances.index'));
        $response->assertStatus(200);
        $response->assertSee("{$workDateLabel}の勤怠");
        $response->assertSee($workDate->format('Y/m/d'));
    }

    public function test_admin_can_view_previous_day_attendance_when_clicking_previous_day_button()
    {
        $admin = Admin::find(1);
        $today = Carbon::today();
        $previousDay = $today->copy()->subDay();

        $attendances = Attendance::where('work_date', $previousDay)
            ->get()
            ->keyBy('user_id');

        $hasAttendance = $attendances->isNotEmpty();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendances.index', ['date' => $previousDay->toDateString()]));
        $response->assertStatus(200);

        $workDateLabel = $previousDay->toJapaneseDate();
        $response->assertSee("{$workDateLabel}の勤怠");
        $response->assertSee($previousDay->format('Y/m/d'));

        if (!$hasAttendance) {
            $response->assertSee('この日の勤怠データはありません');
        } else {
            foreach ($attendances as $attendance) {
                $response->assertSee($attendance->user->name);
                $response->assertSee($attendance?->clock_in?->format('H:i') ?? '');
                $response->assertSee($attendance?->clock_out?->format('H:i') ?? '');
                $response->assertSee($attendance?->total_break ? gmdate('H:i', $attendance->total_break * 60) : '');
                $response->assertSee($attendance?->total_break ? gmdate('H:i', $attendance->total_work * 60) : '');
            }
        }
    }

    public function test_admin_can_view_following_day_attendance_when_clicking_following_day_button()
    {
        $admin = Admin::find(1);
        $today = Carbon::today();
        $followingDay = $today->copy()->addDay();

        $attendances = Attendance::where('work_date', $followingDay)
            ->get()
            ->keyBy('user_id');

        $hasAttendance = $attendances->isNotEmpty();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendances.index', ['date' => $followingDay->toDateString()]));
        $response->assertStatus(200);

        $workDateLabel = $followingDay->toJapaneseDate();
        $response->assertSee("{$workDateLabel}の勤怠");
        $response->assertSee($followingDay->format('Y/m/d'));

        if (!$hasAttendance) {
            $response->assertSee('この日の勤怠データはありません');
        } else {
            foreach ($attendances as $attendance) {
                $response->assertSee($attendance->user->name);
                $response->assertSee($attendance?->clock_in?->format('H:i') ?? '');
                $response->assertSee($attendance?->clock_out?->format('H:i') ?? '');
                $response->assertSee($attendance?->total_break ? gmdate('H:i', $attendance->total_break * 60) : '');
                $response->assertSee($attendance?->total_break ? gmdate('H:i', $attendance->total_work * 60) : '');
            }
        }
    }
}
