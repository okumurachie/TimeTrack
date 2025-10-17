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
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
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
        $this->user = User::find(1);
        $this->attendance = Attendance::where('user_id', $this->user->id)
            ->where('has_request', false)
            ->firstOrFail();
    }

    public function test_admin_sees_correct_attendance_for_selected_user_and_date()
    {
        $breakTimes = BreakTime::where('attendance_id', $this->attendance->id)
            ->get();

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($this->user->name);
        $response->assertSee($this->attendance->work_date->format('Y年'));
        $response->assertSee($this->attendance->work_date->format('n月j日'));
        $response->assertSee($this->attendance->clock_in->format('H:i'));
        $response->assertSee($this->attendance->clock_out->format('H:i'));
        foreach ($breakTimes as $break) {
            $response->assertSee($break->break_start->format('H:i'));
            $response->assertSee($break->break_end->format('H:i'));
        }
    }

    public function test_admin_validation_fails_when_clock_in_is_after_clock_out()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('admin.attendance.update', $this->attendance->id),
            [
                'clock_in' => '12:00',
                'clock_out' => '11:00',
                'reason' => 'Test Reason',
            ]
        );

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
        $response->assertRedirect(route('admin.detail.record', $this->attendance->id));

        $response = $this->get(route('admin.attendance.update', $this->attendance->id));
        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_admin_validation_fails_when_break_start_is_after_clock_out()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        $breaks = [
            ['start' => '16:00', 'end' => '17:00'],
        ];

        $postData = [
            'clock_out' => '15:00',
            'breaks' => $breaks,
            'reason' => 'Test Reason',
        ];

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('admin.attendance.update', $this->attendance->id),
            $postData
        );

        foreach ($breaks as $i => $_) {
            $response->assertSessionHasErrors([
                "breaks.$i.start" => '休憩時間が不適切な値です'
            ]);
        }

        $response->assertRedirect(route('admin.detail.record', $this->attendance->id));

        $response = $this->get(route('admin.attendance.update', $this->attendance->id));
        $response->assertSee('休憩時間が不適切な値です');
    }

    public function test_admin_validation_fails_when_break_end_is_after_clock_out()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        $breaks = [
            ['start' => '17:00', 'end' => '18:30'],
        ];

        $postData = [
            'clock_out' => '18:00',
            'breaks' => $breaks,
            'reason' => 'Test Reason',
        ];

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('admin.attendance.update', $this->attendance->id),
            $postData
        );

        foreach ($breaks as $i => $_) {
            $response->assertSessionHasErrors([
                "breaks.$i.end" => '休憩時間もしくは退勤時間が不適切な値です'
            ]);
        }

        $response->assertRedirect(route('admin.detail.record', $this->attendance->id));

        $response = $this->get(route('admin.attendance.update', $this->attendance->id));
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_admin_validation_fails_when_reason_missing()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('admin.attendance.update', $this->attendance->id),
            [
                'reason' => '',
            ]
        );

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
        $response->assertRedirect(route('admin.detail.record', $this->attendance->id));

        $response = $this->get(route('admin.attendance.update', $this->attendance->id));
        $response->assertSee('備考を記入してください');
    }
}
