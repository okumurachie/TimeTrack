<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_clock_in_button_functions_correctly()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'clock_in',
        ]);

        $response->assertRedirect(route('attendance.index'));

        $followUp = $this->get(route('attendance.index'));
        $followUp->assertStatus(200);
        $followUp->assertSee('出勤中');
    }

    public function test_button_hidden_for_clocked_out_user()
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

        $crawler = new Crawler($response->getContent());

        $this->assertCount(0, $crawler->filter('button:contains("出勤")'));
        $this->assertCount(0, $crawler->filter('button:contains("休憩入")'));
        $this->assertCount(0, $crawler->filter('button:contains("休憩戻")'));
        $this->assertCount(0, $crawler->filter('button:contains("退勤")'));
    }

    public function test_user_can_clock_in_and_attendance_is_recorded_correctly()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('勤務外');

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'clock_in',
        ]);

        $dateString = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date',  $dateString)
            ->first();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => $dateString,
            'clock_in' =>  $attendance->clock_in->format('Y-m-d H:i:s')
        ]);

        $result = $this->actingAs($user)->get(route('my-record.list'));
        $result->assertStatus(200);
        $result->assertSee($attendance->clock_in->format('H:i'));
    }
}
