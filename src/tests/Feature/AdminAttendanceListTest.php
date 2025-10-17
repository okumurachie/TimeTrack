<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AdminsTableSeeder;
use Database\Seeders\AttendanceDataSeeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Correction;
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
        $response->assertSee($workDate->format('Y/m/d'));

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
