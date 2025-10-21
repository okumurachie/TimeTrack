<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AdminsTableSeeder;
use Database\Seeders\AttendanceDataSeeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class AdminUserAttendanceViewTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('config:clear');

        $this->seed(UsersTableSeeder::class);
        $this->seed(AdminsTableSeeder::class);
        $this->seed(AttendanceDataSeeder::class);

        $this->admin = Admin::find(1);
    }

    public function test_admin_can_view_all_users_name_and_email_on_stuff_list()
    {
        $users = User::all();

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.staff.list'));
        $response->assertStatus(200);
        $response->assertSee('スタッフ一覧');

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_admin_can_view_attendances_on_selected_user_attendance_list()
    {
        $user = User::find(1);
        $month = Carbon::now()->format('Y/m');
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get();


        $response = $this->actingAs($this->admin, 'admin')->get(route('staff-record.list', $user->id));
        $response->assertStatus(200);
        $response->assertSee($month);
        $response->assertSee("{$user->name}さんの勤怠");

        foreach ($attendances as $attendance) {
            $workDate = Carbon::parse($attendance->work_date);
            $response->assertSee($workDate->format('m/d') . '(' . $workDate->isoFormat('ddd') . ')');
            $response->assertSee($attendance?->clock_in?->format('H:i') ?? '');
            $response->assertSee($attendance?->clock_out?->format('H:i') ?? '');
            $response->assertSee($attendance?->total_break ? gmdate('H:i', $attendance->total_break * 60) : '');
            $response->assertSee($attendance?->total_work ? gmdate('H:i', $attendance->total_work * 60) : '');
        }
    }

    public function test_admin_can_view_previous_month_attendances_on_selected_user_attendance_list()
    {
        $user = User::find(1);
        $month = Carbon::now()->format('Y/m');
        $currentMonth = Carbon::now()->startOfMonth();

        $previousMonth = $currentMonth->copy()->subMonth()->startOfMonth();
        $previousMonthStart = $currentMonth->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $currentMonth->copy()->subMonth()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$previousMonthStart, $previousMonthEnd])
            ->get();

        $response = $this->actingAs($this->admin, 'admin')->get(route('staff-record.list', $user->id));
        $response->assertStatus(200);
        $response->assertSee($month);
        $response->assertSee("{$user->name}さんの勤怠");

        $response = $this->actingAs($this->admin, 'admin')->get(route('staff-record.list', $user->id) . '?month=' . $previousMonth->format('Y-m'));
        $response->assertStatus(200);
        $response->assertSee($previousMonth->format('Y/m'));
        $response->assertSee("{$user->name}さんの勤怠");

        foreach ($attendances as $attendance) {
            $workDate = Carbon::parse($attendance->work_date);
            $response->assertSee($workDate->format('m/d') . '(' . $workDate->isoFormat('ddd') . ')');
            $response->assertSee($attendance?->clock_in?->format('H:i') ?? '');
            $response->assertSee($attendance?->clock_out?->format('H:i') ?? '');
            $response->assertSee($attendance?->total_break ? gmdate('H:i', $attendance->total_break * 60) : '');
            $response->assertSee($attendance?->total_work ? gmdate('H:i', $attendance->total_work * 60) : '');
        }
    }

    public function test_admin_can_view_following_month_attendances_on_selected_user_attendance_list()
    {
        $user = User::find(1);
        $month = Carbon::now()->format('Y/m');
        $currentMonth = Carbon::now()->startOfMonth();

        $followingMonth = $currentMonth->copy()->addMonth()->startOfMonth();
        $followingMonthStart = $currentMonth->copy()->addMonth()->startOfMonth();
        $followingMonthEnd = $currentMonth->copy()->addMonth()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$followingMonthStart, $followingMonthEnd])
            ->get();

        $response = $this->actingAs($this->admin, 'admin')->get(route('staff-record.list', $user->id));
        $response->assertStatus(200);
        $response->assertSee($month);
        $response->assertSee("{$user->name}さんの勤怠");

        $response = $this->actingAs($this->admin, 'admin')->get(route('staff-record.list', $user->id) . '?month=' . $followingMonth->format('Y-m'));
        $response->assertStatus(200);
        $response->assertSee($followingMonth->format('Y/m'));
        $response->assertSee("{$user->name}さんの勤怠");

        foreach ($attendances as $attendance) {
            $workDate = Carbon::parse($attendance->work_date);
            $response->assertSee($workDate->format('m/d') . '(' . $workDate->isoFormat('ddd') . ')');
            $response->assertSee($attendance?->clock_in?->format('H:i') ?? '');
            $response->assertSee($attendance?->clock_out?->format('H:i') ?? '');
            $response->assertSee($attendance?->total_break ? gmdate('H:i', $attendance->total_break * 60) : '');
            $response->assertSee($attendance?->total_work ? gmdate('H:i', $attendance->total_work * 60) : '');
        }
    }

    public function test_admin_can_view_selected_user_attendance_detail_from_list()
    {
        $user = User::find(1);
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->first();

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.detail.record', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($user->name);

        $workDate = Carbon::parse($attendance->work_date);
        $response->assertSee($workDate->format('Y年'));
        $response->assertSee($workDate->format('n月j日'));
        $response->assertSee($attendance?->clock_in?->format('H:i'));
        $response->assertSee($attendance?->clock_out?->format('H:i'));

        $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get();
        foreach ($breakTimes as $break) {
            $response->assertSee($break->break_start->format('H:i'));
            $response->assertSee($break->break_end->format('H:i'));
        }
    }
}
