<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AdminsTableSeeder;
use Database\Seeders\AttendanceDataSeeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Correction;
use App\Models\BreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class UserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;


    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('config:clear');

        $this->seed(UsersTableSeeder::class);
        $this->seed(AdminsTableSeeder::class);
        $this->seed(AttendanceDataSeeder::class);

        $this->user = User::find(1);
        $this->attendance = Attendance::where('user_id', $this->user->id)
            ->where('has_request', false)
            ->firstOrFail();
    }

    public function test_user_name_is_displayed_on_attendance_detail()
    {
        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($this->user->name);
    }

    public function test_selected_attendance_date_is_displayed_on_detail_page()
    {
        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($this->attendance->work_date->format('Y年'));
        $response->assertSee($this->attendance->work_date->format('n月j日'));
    }

    public function test_user_clock_in_and_out_times_are_displayed_correctly()
    {
        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($this->attendance->clock_in->format('H:i'));
        $response->assertSee($this->attendance->clock_out->format('H:i'));
    }

    public function test_user_break_times_are_displayed_correctly()
    {
        $breakTimes = BreakTime::where('attendance_id', $this->attendance->id)
            ->get();

        $response = $this->actingAs($this->user)->get(route('user.detail.record', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

        foreach ($breakTimes as $break) {
            $response->assertSee($break->break_start->format('H:i'));
            $response->assertSee($break->break_end->format('H:i'));
        }
    }
}
