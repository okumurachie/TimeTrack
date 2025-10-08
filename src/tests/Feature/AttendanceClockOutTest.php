<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_out_button_functions_correctly()
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
        $crawler = new Crawler($response->getContent());
        $this->assertCount(1, $crawler->filter('button:contains("退勤")'));

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'clock_out',
        ]);

        $response->assertRedirect(route('attendance.index'));

        $followUp = $this->get(route('attendance.index'));
        $followUp->assertStatus(200);
        $followUp->assertSee('退勤済');
    }

    public function test_user_can_clock_out_and_attendance_is_recorded_correctly()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('勤務外');

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'clock_in',
        ]);
        $response->assertRedirect(route('attendance.index'));

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'clock_out',
        ]);
        $response->assertRedirect(route('attendance.index'));

        $dateString = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date',  $dateString)
            ->first();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => $dateString,
            'clock_in' =>  $attendance->clock_in->format('Y-m-d H:i:s'),
            'clock_out' =>  $attendance->clock_out->format('Y-m-d H:i:s')
        ]);

        $result = $this->actingAs($user)->get(route('my-record.list'));
        $result->assertStatus(200);
        $result->assertSee($attendance->clock_out->format('H:i'));
    }
}
