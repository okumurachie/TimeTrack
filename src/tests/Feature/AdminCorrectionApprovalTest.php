<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AdminsTableSeeder;
use Database\Seeders\AttendanceDataSeeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Correction;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class AdminCorrectionApprovalTest extends TestCase
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

    public function test_admin_can_see_all_pending_correction_requests()
    {
        $users = User::all();

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.correction.list'));
        $response->assertStatus(200);
        $response->assertSee('申請一覧');

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.correction.list', ['tab' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee('申請一覧');

        foreach ($users as $user) {
            $corrections = Correction::where('user_id', $user->id)
                ->where('status', 'pending')
                ->get();

            foreach ($corrections as $correction) {
                $response->assertSee('承認待ち');
                $response->assertSee($user->name);
                $response->assertSee($correction->attendance->work_date->format('Y/m/d'));
                $response->assertSee($correction->reason);
                $response->assertSee($correction->created_at->format('Y/m/d'));
            }
        }
    }

    public function test_admin_can_see_all_approved_correction_requests()
    {
        $users = User::all();

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.correction.list', ['tab' => 'approved']));
        $response->assertStatus(200);
        $response->assertSee('申請一覧');

        foreach ($users as $user) {
            $corrections = Correction::where('user_id', $user->id)
                ->where('status', 'approved')
                ->get();

            foreach ($corrections as $correction) {
                $response->assertSee('承認済み');
                $response->assertSee($user->name);
                $response->assertSee($correction->attendance->work_date->format('Y/m/d'));
                $response->assertSee($correction->reason);
                $response->assertSee($correction->created_at->format('Y/m/d'));
            }
        }
    }

    public function test_admin_can_see_correct_correction_request_details()
    {
        $correction = Correction::where('status', 'pending')->first();
        $user = $correction->user;

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.correction.list', ['tab' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee('申請一覧');

        $response = $this->actingAs($this->admin, 'admin')->get(route('correction.approval.show', $correction->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($user->name);
        $response->assertSee($correction->attendance->work_date->format('Y年'));
        $response->assertSee($correction->attendance->work_date->format('n月j日'));
        $response->assertSee($correction->reason);

        $changes = $correction->changes;
        foreach ($changes as $field => $value) {
            if ($field === 'clock_in' || $field === 'clock_out') {
                $formattedValue = Carbon::parse($value)->format('H:i');
                $response->assertSee($formattedValue);
            } elseif ($field === 'break_times') {
                foreach ($value as $breakTime) {
                    $start = Carbon::parse($breakTime['start'])->format('H:i');
                    $end = Carbon::parse($breakTime['end'])->format('H:i');
                    $response->assertSee($start);
                    $response->assertSee($end);
                }
            }
        }
    }

    public function test_admin_approving_correction_request_updates_attendance()
    {
        $user = User::find(1);
        $correction = Correction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        $changes = $correction->changes;
        $attendance = $correction->attendance;
        $date = $attendance->work_date->toDateString();

        $clockIn = !empty($changes['clock_in']) ? Carbon::parse($date . ' ' . $changes['clock_in']) : $attendance->clock_in;
        $clockOut = !empty($changes['clock_out']) ? Carbon::parse($date . ' ' . $changes['clock_out']) : $attendance->clock_out;

        if (!empty($changes['breaks'])) {
            $attendance->breakTimes()->delete();

            $breaks = collect($changes['breaks'])->map(function ($break) use ($attendance) {
                $workDate = Carbon::parse($attendance->work_date);
                return [
                    'break_start' => !empty($break['start']) ? $workDate->copy()->setTimeFromTimeString($break['start']) : null,
                    'break_end' => !empty($break['end']) ? $workDate->copy()->setTimeFromTimeString($break['end']) : null,
                ];
            })->toArray();

            $attendance->breakTimes()->createMany($breaks);
            $attendance->load('breakTimes');
        }


        $response = $this->actingAs($this->admin, 'admin')->get(route('correction.approval.show', $correction->id));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($user->name);
        $response->assertSee($correction->attendance->work_date->format('Y年'));
        $response->assertSee($correction->attendance->work_date->format('n月j日'));
        $response->assertSee($correction->reason);
        $response->assertSee('承認');

        $workMinutes = ($clockIn && $clockOut) ? $clockIn->diffInMinutes($clockOut) : 0;

        $breakMinutes = $attendance->breakTimes->sum(function ($break) {
            $start = $break->break_start ? Carbon::parse($break->break_start) : null;
            $end = $break->break_end ? Carbon::parse($break->break_end) : null;
            return ($start && $end) ? $start->diffInMinutes($end) : 0;
        });

        $totalWork = $workMinutes - $breakMinutes;

        $response = $this->actingAs($this->admin, 'admin')->post(
            route('correction.approval', $correction->id),
            [
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'breaks' => $breaks,
                'reason' => $correction->reason,
            ]
        );

        $response->assertRedirect(route('correction.approval.show', $correction->id));
        $this->assertDatabaseHas('corrections', [
            'id' => $correction->id,
            'admin_id' => $this->admin->id,
            'status' => 'approved',
        ]);

        $attendance->refresh();

        $this->assertEquals($clockIn, $attendance->clock_in);
        $this->assertEquals($clockOut, $attendance->clock_out);
        $this->assertEquals($totalWork, $attendance->total_work);
        $this->assertEquals($breakMinutes, $attendance->total_break);

        $response = $this->actingAs($this->admin, 'admin')->get(route('correction.approval.show', $correction->id));
        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertDontSee('<button class="form__button__submit">承認</button>', false);
    }
}
