<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
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
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get();


        $response = $this->actingAs($this->admin, 'admin')->get(route('staff-record.list', $user->id));
        $response->assertStatus(200);
        $response->assertSee('スタッフ一覧');
        $response->assertSee("{$user->name}さんの勤怠");

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->work_date->format('m/d') . '(' . $attendance->work_date->isoFormat('ddd') . ')');
            $response->assertSee($attendance?->clock_in?->format('H:i') ?? '');
            $response->assertSee($attendance?->clock_out?->format('H:i') ?? '');
            $response->assertSee($attendance?->total_break ? gmdate('H:i', $attendance->total_break * 60) : '');
            $response->assertSee($attendance?->total_work ? gmdate('H:i', $attendance->total_work * 60) : '');
        }
    }
}
