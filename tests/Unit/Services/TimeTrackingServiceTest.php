<?php

namespace Tests\Unit\Services;

use App\Exceptions\TimeClocks\TimeClockAlreadyStartedException;
use App\Models\Session;
use App\Models\TimeClock;
use App\Services\TimeTrackingService;
use App\TimeClockType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TimeTrackingServiceTest extends TestCase
{
    public function test_it_can_start_a_time_clock(): void
    {
        // Arrange
        $session = Session::factory()->create();
        $timeTrackingService = new TimeTrackingService($session);

        // Act
        $timeTrackingService->startTracking(TimeClockType::OnDuty);

        // Assert
        $this->assertDatabaseHas('time_clocks', [
            'session_id' => $session->id,
            'type' => TimeClockType::OnDuty,
            'started_at' => now(),
        ]);
    }

    public function test_it_can_switch_to_another_time_clock(): void
    {
        Carbon::setTestNow(now());

        // Arrange
        $session = Session::factory()->create();
        $timeTrackingService = new TimeTrackingService($session);
        TimeClock::factory()->for($session)->create([
            'type' => TimeClockType::OnDuty,
            'started_at' => now()->subMinutes(30),
        ]);

        // Act
        $timeTrackingService->startTracking(TimeClockType::OffDuty);

        // Assert
        $this->assertDatabaseHas('time_clocks', [
            'session_id' => $session->id,
            'type' => TimeClockType::OnDuty,
            'duration' => 30,
            'started_at' => now()->subMinutes(30),
            'ended_at' => now(),
        ]);

        $this->assertDatabaseHas('time_clocks', [
            'session_id' => $session->id,
            'type' => TimeClockType::OffDuty,
            'started_at' => now(),
        ]);
    }

    public function test_it_cannot_start_the_same_time_clock_twice(): void
    {
        // Expect
        $this->expectException(TimeClockAlreadyStartedException::class);

        // Arrange
        $session = Session::factory()->create();
        $timeTrackingService = new TimeTrackingService($session);
        TimeClock::factory()->for($session)->create([
            'type' => TimeClockType::OnDuty,
            'started_at' => now()->subMinutes(30),
        ]);

        // Act
        $timeTrackingService->startTracking(TimeClockType::OnDuty);
    }
}
