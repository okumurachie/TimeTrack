<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AdminsTableSeeder;
use Database\Seeders\AttendanceDataSeeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Correction;
use App\Models\BreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class UserCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('admins')->truncate();
        DB::table('attendances')->truncate();
        DB::table('break_times')->truncate();
        DB::table('corrections')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->seed(UsersTableSeeder::class);
        $this->seed(AdminsTableSeeder::class);
        $this->seed(AttendanceDataSeeder::class);

        $this->user = User::find(1);
        $this->attendance = Attendance::where('user_id', $this->user->id)
            ->where('has_request', false)
            ->firstOrFail();
    }

    public function test_validation_fails_when_clock_in_is_after_clock_out()
    {
        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        $response = $this->post(
            route('attendance.request', $this->attendance->id),
            [
                'clock_in' => '12:00',
                'clock_out' => '11:00',
                'reason' => 'Test Reason',
            ]
        );

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
        $response->assertRedirect(route('user.detail.record', $this->attendance->id));

        $response = $this->get(route('attendance.request', $this->attendance->id));
        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_validation_fails_when_break_start_is_after_clock_out()
    {
        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
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

        $response = $this->post(
            route('attendance.request', $this->attendance->id),
            $postData
        );

        foreach ($breaks as $i => $_) {
            $response->assertSessionHasErrors([
                "breaks.$i.start" => '休憩時間が不適切な値です',
            ]);
        }

        $response->assertRedirect(route('user.detail.record', $this->attendance->id));

        $response = $this->get(route('attendance.request', $this->attendance->id));
        $response->assertSee('休憩時間が不適切な値です');
    }

    public function test_validation_fails_when_break_end_is_after_clock_out()
    {
        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        $breaks = [
            ['start' => '16:00', 'end' => '17:00'],
        ];

        $postData = [
            'clock_out' => '16:30',
            'breaks' => $breaks,
            'reason' => 'Test Reason',
        ];

        $response = $this->post(
            route('attendance.request', $this->attendance->id),
            $postData
        );

        foreach ($breaks as $i => $_) {
            $response->assertSessionHasErrors([
                "breaks.$i.end" => '休憩時間もしくは退勤時間が不適切な値です',
            ]);
        }

        $response->assertRedirect(route('user.detail.record', $this->attendance->id));

        $response = $this->get(route('attendance.request', $this->attendance->id));
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_validation_fails_when_reason_missing()
    {
        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        $response = $this->post(
            route('attendance.request', $this->attendance->id),
            [
                'reason' => '',
            ]
        );

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
        $response->assertRedirect(route('user.detail.record', $this->attendance->id));

        $response = $this->get(route('attendance.request', $this->attendance->id));
        $response->assertSee('備考を記入してください');
    }

    public function test_correction_request_is_created_successfully()
    {
        $admin = Admin::find(1);
        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        $breaks = [
            ['start' => '12:30', 'end' => '13:30'],
        ];

        $postData = [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'clock_in' => '09:10',
            'clock_out' => '18:10',
            'breaks' => $breaks,
            'reason' => '電車遅延のため',
        ];

        $response = $this->post(
            route('attendance.request', $this->attendance->id),
            $postData
        );
        $this->assertDatabaseHas('corrections', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'reason' => '電車遅延のため',
        ]);

        $response->assertRedirect(route('user.detail.record', $this->attendance->id));

        $correction = Correction::where('attendance_id', $this->attendance->id)->first();
        $this->assertEquals('09:10', $correction->changes['clock_in']);
        $this->assertEquals('18:10', $correction->changes['clock_out']);
        $this->assertEquals([
            ['start' => '12:30', 'end' => '13:30'],
        ], $correction->changes['breaks']);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.correction.list'));

        $response->assertStatus(200);
        $response->assertSee('申請一覧');
        $response->assertSee('承認待ち');
        $response->assertSee($correction->user->name);
        $response->assertSee($correction->attendance->work_date->format('Y/m/d'));
        $response->assertSee($correction->reason);
        $response->assertSee($correction->created_at->format('Y/m/d'));

        $response = $this->actingAs($admin, 'admin')->get(route('correction.approval.show', $correction->id));

        $date = $correction->attendance->work_date->toDateString();
        $workDate = Carbon::parse($date);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($this->user->name);
        $response->assertSee($workDate->format('Y年'));
        $response->assertSee($workDate->format('n月j日'));
        $response->assertSee($correction->reason);
    }

    public function test_user_can_view_all_their_pending_approval_requests()
    {
        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        $breaks = [
            ['start' => '12:30', 'end' => '13:30'],
        ];

        $postData = [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'clock_in' => '09:10',
            'clock_out' => '18:10',
            'breaks' => $breaks,
            'reason' => '電車遅延のため',
        ];

        $response = $this->post(
            route('attendance.request', $this->attendance->id),
            $postData
        );

        $this->assertDatabaseHas('corrections', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'reason' => '電車遅延のため',
        ]);

        $corrections = Correction::where('user_id', $this->user->id)
            ->where('status', 'pending')
            ->get();

        $response = $this->actingAs($this->user)->get(route('user.correction.list', ['tab' => 'pending']));

        foreach ($corrections as $correction) {
            $response->assertSee('承認待ち');
            $response->assertSee($this->user->name);
            $response->assertSee($correction->attendance->work_date->format('Y/m/d'));
            $response->assertSee($correction->reason);
            $response->assertSee($correction->created_at->format('Y/m/d'));
        }
    }

    public function test_user_can_view_all_their_approved_requests()
    {
        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        $breaks = [
            ['start' => '12:30', 'end' => '13:30'],
        ];

        $postData = [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'clock_in' => '09:10',
            'clock_out' => '18:10',
            'breaks' => $breaks,
            'reason' => '電車遅延のため',
        ];

        $response = $this->post(
            route('attendance.request', $this->attendance->id),
            $postData
        );

        $this->assertDatabaseHas('corrections', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'reason' => '電車遅延のため',
        ]);

        $corrections = Correction::where('user_id', $this->user->id)
            ->where('status', 'approved')
            ->get();

        $response = $this->actingAs($this->user)->get(route('user.correction.list', ['tab' => 'approved']));

        foreach ($corrections as $correction) {
            $response->assertSee('承認済み');
            $response->assertSee($this->user->name);
            $response->assertSee($correction->attendance->work_date->format('Y/m/d'));
            $response->assertSee($correction->reason);
            $response->assertSee($correction->created_at->format('Y/m/d'));
        }
    }
}
