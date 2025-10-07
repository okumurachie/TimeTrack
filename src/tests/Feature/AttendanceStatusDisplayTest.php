<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceStatusDisplayTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_displays_off_duty_status_when_user_not_working()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_displays_on_duty_status_when_user_working()
    {
        $user = User::factory()->create();
        $dateString = Carbon::today()->toDateString();
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $dateString,
            'clock_in' => $dateString . ' 09:00:00',
            'clock_out' => null,
            'total_break' => 0,
            'total_work' => 0,
            'is_on_break' => false,
            'has_request' => false,
        ]);
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_displays_on_break_status_when_user_on_break()
    {
        $user = User::factory()->create();
        $dateString = Carbon::today()->toDateString();
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $dateString,
            'clock_in' => $dateString . ' 09:00:00',
            'clock_out' => null,
            'total_break' => 0,
            'total_work' => 0,
            'is_on_break' => true,
            'has_request' => false,
        ]);
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_displays_clocked_out_status_when_user_clocked_out()
    {
        $user = User::factory()->create();
        $dateString = Carbon::today()->toDateString();
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $dateString,
            'clock_in' => $dateString . ' 09:00:00',
            'clock_out' => $dateString . ' 18:00:00',
            'total_break' => 60,
            'total_work' => 480,
            'is_on_break' => false,
            'has_request' => false,
        ]);
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
