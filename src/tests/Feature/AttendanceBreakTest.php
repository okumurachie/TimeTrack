<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $dateString;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->dateString = Carbon::today()->toDateString();
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->dateString,
            'clock_in' => $this->dateString . ' 09:00:00',
            'clock_out' => null,
            'total_break' => 0,
            'total_work' => 0,
            'is_on_break' => false,
            'has_request' => false,
        ]);
    }

    public function test_break_start_button_functions_correctly()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        $crawler = new Crawler($response->getContent());
        $this->assertCount(1, $crawler->filter('button:contains("休憩入")'));

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'break_start',
        ]);
        $response->assertRedirect(route('attendance.index'));

        $followUp = $this->get(route('attendance.index'));
        $followUp->assertStatus(200);
        $followUp->assertSee('休憩中');
    }

    public function test_user_can_take_multiple_breaks_in_a_day()
    {
        $response = $this->actingAs($this->user);

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'break_start'
        ]);
        $response->assertRedirect(route('attendance.index'));
        $response = $this->post(route('attendance.stamp'), [
            'action' => 'break_end'
        ]);
        $response->assertRedirect(route('attendance.index'));

        $followUp = $this->get(route('attendance.index'));
        $followUp->assertStatus(200);

        $crawler = new Crawler($followUp->getContent());
        $this->assertCount(1, $crawler->filter('button:contains("休憩入")'));
    }

    public function test_break_end_button_functions_correctly()
    {
        $response = $this->actingAs($this->user);

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'break_start'
        ]);
        $response->assertRedirect(route('attendance.index'));

        $followUp = $this->get(route('attendance.index'));
        $followUp->assertStatus(200);
        $crawler = new Crawler($followUp->getContent());
        $this->assertCount(1, $crawler->filter('button:contains("休憩戻")'));

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'break_end'
        ]);
        $response->assertRedirect(route('attendance.index'));

        $result = $this->get(route('attendance.index'));
        $result->assertStatus(200);
        $result->assertSee('出勤中');
    }

    public function test_user_can_end_multiple_breaks_in_a_day()
    {
        $response = $this->actingAs($this->user);

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'break_start'
        ]);
        $response->assertRedirect(route('attendance.index'));
        $response = $this->post(route('attendance.stamp'), [
            'action' => 'break_end'
        ]);
        $response->assertRedirect(route('attendance.index'));
        $response = $this->post(route('attendance.stamp'), [
            'action' => 'break_start'
        ]);
        $response->assertRedirect(route('attendance.index'));

        $followUp = $this->get(route('attendance.index'));
        $followUp->assertStatus(200);
        $crawler = new Crawler($followUp->getContent());
        $this->assertCount(1, $crawler->filter('button:contains("休憩戻")'));
    }

    public function test_user_can_take_breaks_and_attendance_recorded_correctly()
    {
        $response = $this->actingAs($this->user);

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'break_start'
        ]);
        $response->assertRedirect(route('attendance.index'));

        $response = $this->post(route('attendance.stamp'), [
            'action' => 'break_end'
        ]);
        $response->assertRedirect(route('attendance.index'));

        $attendance = Attendance::where('user_id', $this->user->id)
            ->where('work_date',  $this->dateString)
            ->first();

        $breakTime = BreakTime::where('attendance_id', $attendance->id)->first();

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_start' =>  $breakTime->break_start->format('Y-m-d H:i:s'),
            'break_end' => $breakTime->break_end->format('Y-m-d H:i:s'),
        ]);

        $result = $this->get(route('my-record.list'));
        $result->assertStatus(200);
    }
}
