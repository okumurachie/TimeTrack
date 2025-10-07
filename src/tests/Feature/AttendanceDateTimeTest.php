<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;


class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_attendance_page_displays_current_datetime()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee(now()->isoFormat('YYYY年MM月DD日(dd)'));
        $response->assertSee(now()->format('H:i'));
    }
}
