<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\AdminsTableSeeder;
use Database\Seeders\AttendanceDataSeeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Correction;
use App\Models\Admin;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Tests\TestCase;

class UserAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminsTableSeeder::class);
    }

    public function test_user_can_view_all_of_their_own_attendances()
    {
        $user =  User::factory()->create();

        $this->seed(AttendanceDataSeeder::class);

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get();

        $response = $this->actingAs($user)->get(route('my-record.list'));
        $response->assertStatus(200);
        $response->assertSee('勤怠一覧');

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->work_date->format('m/d') . '(' . $attendance->work_date->isoFormat('ddd') . ')');
        }
    }

    public function test_user_sees_current_month_attendance_on_list_page()
    {
        $user = User::factory()->create();

        $month = Carbon::now()->format('Y-m');
        $currentMonth = Carbon::createFromFormat('Y-m', $month);

        $response = $this->actingAs($user)->get(route('my-record.list'));
        $response->assertStatus(200);
        $response->assertSee('勤怠一覧');
        $response->assertSee($currentMonth->format('Y/m'));
    }

    public function test_user_can_view_previous_month_attendance()
    {
        $user = User::factory()->create([
            'id' => 1,
        ]);

        $this->seed(AttendanceDataSeeder::class);

        $month = Carbon::now()->format('Y-m');
        $currentMonth = Carbon::createFromFormat('Y-m', $month);
        $previousMonth = $currentMonth->copy()->subMonth()->startOfMonth();
        $monthParm = $previousMonth->format('Y-m');

        $response = $this->actingAs($user)->get(route('my-record.list', ['month' => $monthParm]));
        $response->assertStatus(200);
        $response->assertSee($previousMonth->format('Y/m'));

        $startOfMonth = $previousMonth->copy()->startOfMonth()->startOfDay();
        $endOfMonth = $previousMonth->copy()->endOfMonth()->startOfDay();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get();

        $this->assertNotEmpty($attendances);

        foreach ($attendances as $attendance) {
            $date = Carbon::parse($attendance->work_date);
            $response->assertSee($date->format('m/d') . '(' . $date->isoFormat('ddd') . ')');
        }
    }

    public function test_user_can_view_following_month_attendance()
    {
        $user = User::factory()->create([
            'id' => 1,
        ]);

        $this->seed(AttendanceDataSeeder::class);

        $month = Carbon::now()->format('Y-m');
        $currentMonth = Carbon::createFromFormat('Y-m', $month);
        $followingMonth = $currentMonth->copy()->addMonth()->startOfMonth();
        $monthParm = $followingMonth->format('Y-m');

        $response = $this->actingAs($user)->get(route('my-record.list', ['month' => $monthParm]));
        $response->assertStatus(200);
        $response->assertSee($followingMonth->format('Y/m'));

        $startOfMonth = $followingMonth->copy()->startOfMonth()->startOfDay();
        $endOfMonth = $followingMonth->copy()->endOfMonth()->startOfDay();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get();

        $this->assertNotEmpty($attendances);

        foreach ($attendances as $attendance) {
            $date = Carbon::parse($attendance->work_date);
            $response->assertSee($date->format('m/d') . '(' . $date->isoFormat('ddd') . ')');
        }
    }

    public function test_user_can_view_attendance_detail_from_list()
    {
        $user = User::factory()->create([
            'id' => 1,
        ]);

        $this->seed(AttendanceDataSeeder::class);

        $startOfMonth = Carbon::now()->startOfMonth();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $startOfMonth)
            ->first();

        $breakTimes = BreakTime::where('attendance_id', $attendance->id)
            ->get();

        $correction = Correction::where('attendance_id', $attendance->id)
            ->first();

        $response = $this->actingAs($user)->get(route('my-record.list'));
        $response->assertStatus(200);
        $response->assertSee('勤怠一覧');

        $response = $this->actingAs($user)->get(route('user.detail.record', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee([
            '勤怠詳細',
            $user->name,
            $attendance->work_date->format('Y年'),
            $attendance->work_date->format('n月j日'),
            $attendance->clock_in->format('H:i'),
            $attendance->clock_out->format('H:i'),
        ]);

        foreach ($breakTimes as $break) {
            $response->assertSee($break->break_start->format('H:i'));
            $response->assertSee($break->break_end->format('H:i'));
        }

        if ($correction) {
            $response->assertSee($correction->reason);
        }
    }
}
